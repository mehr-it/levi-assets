<?php


	namespace MehrIt\LeviAssets;


	use Illuminate\Http\Request;
	use Illuminate\Support\Arr;
	use Illuminate\Support\Str;
	use InvalidArgumentException;
	use MehrIt\LeviAssets\Contracts\AssetBuilder;
	use MehrIt\LeviAssets\Contracts\AssetsCollection;
	use MehrIt\LeviAssetsLinker\AssetLinker;
	use Nyholm\Psr7\Factory\Psr17Factory;
	use Psr\Http\Message\RequestInterface;
	use RuntimeException;
	use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

	/**
	 * Class AssetsManager
	 * @package MehrIt\LeviAssets
	 */
	class AssetsManager
	{

		/**
		 * @var array
		 */
		protected $collections = [];


		protected $builders = [];

		protected $cachedRequest = null;

		/**
		 * @var \MehrIt\LeviAssetsLinker\Contracts\AssetLinker
		 */
		protected $linker;

		/**
		 * @var array
		 */
		protected $linkerConfig;

		/**
		 * Creates a new instance
		 * @param array $linkerConfig The linker configuration
		 */
		public function __construct(array $linkerConfig = []) {
			$this->linkerConfig = $linkerConfig;
		}


		/**
		 * Registers a new builder
		 * @param string $name The name
		 * @param string $class The builder class
		 * @return $this
		 */
		public function registerBuilder(string $name, string $class): AssetsManager {

			$this->builders[$name] = $class;

			return $this;
		}

		/**
		 * Registers a new assets collection
		 * @param string $name The name
		 * @param array $config The config
		 * @return $this
		 */
		public function registerCollection(string $name, array $config): AssetsManager {

			$this->collections[$name] = $config;

			return $this;
		}

		/**
		 * Creates a link for the given asset
		 * @param string $collection The collection name
		 * @param string $path The asset path
		 * @param string|\Closure|string[]|callable[] $pathFilters The path filters to apply
		 * @param string|string[] $linkFilters The link filter definitions
		 * @param Request|null $request The request to resolve the asset link for
		 * @param string|array|null $query The query to parameters to add to the link
		 * @return string|null The link URL. Null if not resolvable
		 */
		public function link(string $collection, string $path, $pathFilters = [], $linkFilters = [], Request $request = null, $query = null): ?string {

			$psrRequest = $request ?
				$this->toPsrRequest($request) :
				$this->cachedRequest();

			$collectionInstance = $this->collection($collection);
			if (!$collectionInstance)
				throw new InvalidArgumentException("Collection with name \"{$collection}\" is not registered.");


			return $this->linker()->linkAsset(
				$psrRequest,
				$this->resolve($collection, $path, $pathFilters),
				array_merge(
					$collectionInstance->linkFilters(),
					$this->parseLinkFilters($linkFilters)
				),
				$query
			);
		}


		/**
		 * Resolves the asset builds for the given collection and path
		 * @param string $collection The collection name
		 * @param string $path The asset path
		 * @param string|\Closure|string[]|callable[] $filters The filters to apply
		 * @return string[] The asset public paths. Built name as prefix.
		 */
		public function resolve(string $collection, string $path, $filters = []): array {

			$collectionInstance = $this->collection($collection);
			if (!$collectionInstance)
				throw new InvalidArgumentException("Collection with name \"{$collection}\" is not registered.");


			$builtPaths = $collectionInstance->resolve($path);

			if ($filters) {
				if (is_string($filters))
					$filters = explode('|', $filters);

				foreach(Arr::wrap($filters) as $currFilter) {

					if (is_string($currFilter)) {

						$currArgs   = explode(':', $currFilter, 2);
						$filterName = $currArgs[0];
						$filterArgs = ($currArgs[1] ?? null) !== null ? array_map('trim', explode(',', $currArgs[1])) : [];

						switch($filterName) {
							case 'pfx':
								$currFilter = function($builtName) use ($filterArgs) {
									foreach($filterArgs as $currPfx) {
										if (Str::startsWith($builtName, $currPfx))
											return true;
									}

									return false;
								};
								break;

							case 'sfx':
								$currFilter = function($builtName) use ($filterArgs) {
									foreach($filterArgs as $currPfx) {
										if (Str::endsWith($builtName, $currPfx))
											return true;
									}

									return false;
								};
								break;

							default:
								$matchList = array_map('trim', explode(',', $currArgs[0]));

								$currFilter = function($builtName) use ($matchList) {
									return in_array($builtName, $matchList);
								};
								break;
						}
					}
					elseif (!is_callable($currFilter)) {
						throw new InvalidArgumentException('Filter must be instance of string or callable.');

					}

					$filtered = [];
					foreach($builtPaths as $builtName => $path) {
						if (call_user_func($currFilter, $builtName, $path))
							$filtered[$builtName] = $path;
					}
					$builtPaths = $filtered;

				}
			}


			return $builtPaths;
		}

		/**
		 * Gets the collection instance with the given name
		 * @param string $name The name
		 * @return AssetsCollection|null The collection instance or null if not registered
		 */
		public function collection(string $name): ?AssetsCollection {

			$collection = $this->collections[$name] ?? null;
			if ($collection === null)
				return null;

			if (is_array($collection)) {
				$cls = $collection['class'] ?? AssetsCollection::class;

				$collection = app($cls, [
					'config'  => $collection,
					'manager' => $this,
				]);

				if (!($collection instanceof AssetsCollection))
					throw new RuntimeException('Expected instance of ' . AssetsCollection::class . " when resolving \"{$cls}\".");

				$this->collections[$name] = $collection;
			}

			return $collection;
		}

		/**
		 * Gets the builder with the given name
		 * @param string $name The builder name
		 * @return AssetBuilder|null The builder instance or null if not existing
		 */
		public function builder(string $name): ?AssetBuilder {

			$compiler = $this->builders[$name] ?? null;
			if (!$compiler)
				return null;

			if (is_string($compiler)) {

				$compiler = app($compiler);

				if (!($compiler instanceof AssetBuilder))
					throw new RuntimeException('Expected instance of ' . AssetBuilder::class . " when resolving \"{$name}\".");

				$this->builders[$name] = $compiler;
			}

			return $compiler;
		}

		/**
		 * Returns the cached version of the current request
		 * @return RequestInterface The cached version of the current request
		 */
		protected function cachedRequest(): RequestInterface  {
			if (!$this->cachedRequest) {

				$request = request();
				if (!$request)
					throw new RuntimeException('No current request set. Running in console?');

				$this->cachedRequest = $this->toPsrRequest($request);

			}

			return $this->cachedRequest;
		}

		/**
		 * Converts a laravel request to a PSR-7 request
		 * @param Request $request The laravel request
		 * @return RequestInterface THe PSR-7 request
		 */
		protected function toPsrRequest(Request $request): RequestInterface {
			$psr17Factory = new Psr17Factory();

			return (new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory))->createRequest($request);
		}

		/**
		 * Gets a linker instance
		 * @return \MehrIt\LeviAssetsLinker\Contracts\AssetLinker
		 */
		protected function linker(): \MehrIt\LeviAssetsLinker\Contracts\AssetLinker {

			if (!$this->linker) {

				AssetLinker::configure($this->linkerConfig);

				$this->linker = AssetLinker::instance();
			}

			return $this->linker;
		}

		/**
		 * Parses the link filters
		 * @param string|string[] $linkFilters The link filters
		 * @return string[] The link filters
		 */
		protected function parseLinkFilters($linkFilters): array {

			if (is_string($linkFilters))
				$linkFilters = explode('|', $linkFilters);

			if (!$linkFilters)
				return [];

			if (!is_array($linkFilters))
				throw new InvalidArgumentException('Link filters must be an array or a string');

			$filters = [];
			foreach($linkFilters as $currFilterDefinition) {
				$currArgs   = explode(':', $currFilterDefinition, 2);
				$filterName = trim($currArgs[0]);
				$filterArgs = ($currArgs[1] ?? null) !== null ? array_map('trim', explode(',', $currArgs[1])) : [];

				$filters[] = array_merge([$filterName], $filterArgs);
			}


			return $filters;
		}
	}