<?php


	namespace MehrIt\LeviAssets\Collection;


	use Illuminate\Contracts\Filesystem\FileNotFoundException;
	use Illuminate\Contracts\Filesystem\Filesystem;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;
	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviAssets\Contracts\AssetBuilder;
	use MehrIt\LeviAssets\Contracts\AssetsCollection as AssetsCollectionContract;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use RuntimeException;

	class AssetsCollection implements AssetsCollectionContract
	{

		/**
		 * @var array
		 */
		protected $config;

		/**
		 * @var AssetsManager
		 */
		protected $manager;

		/**
		 * @var string|null
		 */
		protected $storagePath;

		/**
		 * @var string|null
		 */
		protected $publicPath;

		/**
		 * @var Filesystem|string|null
		 */
		protected $storage;

		/**
		 * @var Filesystem|string|null
		 */
		protected $publicStorage;

		/**
		 * @var string|null
		 */
		protected $publicStorageName;

		/**
		 * @var string|null
		 */
		protected $storageName;


		/**
		 * @var array|null
		 */
		protected $builders;

		/**
		 * @var array|null
		 */
		protected $linkFilters;

		/**
		 * @var VirusScanner
		 */
		protected $virusScan;

		/**
		 * AssetsCollection constructor.
		 * @param array $config
		 * @param AssetsManager $manager
		 */
		public function __construct(array $config, AssetsManager $manager) {
			$this->config  = $config;
			$this->manager = $manager;

			$this->storagePath       = $config['storage_path'] ?? null;
			$this->publicPath        = $config['public_path'] ?? null;
			$this->storageName       = $config['storage'] ?? null;
			$this->publicStorageName = $config['public_storage'] ?? 'public';
		}

		/**
		 * Gets the config
		 * @return array The config
		 */
		public function getConfig(): array {
			return $this->config;
		}

		/**
		 * Gets the name of the public storage disk
		 * @return string|null The name of the public storage disk
		 */
		public function getPublicStorageName(): ?string {
			return $this->publicStorageName;
		}

		/**
		 * Gets the name of the storage disk
		 * @return string|null The storage disk
		 */
		public function getStorageName(): ?string {
			return $this->storageName;
		}
		
		

		/**
		 * Gets the link filters to apply to the current collection
		 * @return array The filter definitions. Each definition is an array with the filter name followed by any arguments
		 */
		public function linkFilters(): array {

			if ($this->linkFilters === null) {

				$filters = [];
				foreach ($this->config['link_filters'] ?? [] as $currFilter) {
					$currArgs   = explode(':', $currFilter, 2);
					$filterName = trim($currArgs[0]);
					$filterArgs = ($currArgs[1] ?? null) !== null ? array_map('trim', explode(',', $currArgs[1])) : [];

					$filters[] = array_merge([$filterName], $filterArgs);
				}

				$this->linkFilters = $filters;
			}

			return $this->linkFilters;
		}


		/**
		 * @inheritDoc
		 */
		public function resolve(string $path): array {

			return $this->buildPaths($path);
		}

		/**
		 * @inheritDoc
		 */
		public function put(string $path, $resource, bool $publish = true): AssetsCollectionContract {

			// virus scan
			if ($this->config['virus_scan'] ?? true)
				$this->virusScan()->scanStream($resource);

			if (!$this->storage()->put($this->storagePath($path), $resource, ['visibility' => 'private']))
				throw new RuntimeException("Failed to write asset \"{$path}\"");

			// publish
			if ($publish)
				$this->publish($path, true);

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function delete(string $path): AssetsCollectionContract {

			// delete from public storage
			$this->withdraw($path);

			// delete
			$this->storage()->delete($this->storagePath($path));

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function get(string $path) {
			try {
				return $this->storage()->readStream($this->storagePath($path));
			}
			catch (FileNotFoundException $ex) {
				return null;
			}
		}

		/**
		 * @inheritDoc
		 */
		public function exists(string $path): bool {
			return $this->storage()->exists($this->storagePath($path));
		}

		/**
		 * @inheritDoc
		 */
		public function iterate(): iterable {

			$pfxLength = strlen($this->storagePath('/'));

			foreach ($this->storage()->allFiles($this->storagePath('/')) as $currFile) {
				yield ltrim(substr($currFile, $pfxLength), '/');
			}
		}

		/**
		 * @inheritDoc
		 */
		public function iteratePublic(): iterable {
			$pfxLength = strlen($this->publicPath('/'));

			foreach ($this->publicStorage()->allFiles($this->publicPath('/')) as $currFile) {
				yield ltrim(substr($currFile, $pfxLength), '/');
			}
		}

		/**
		 * @inheritDoc
		 */
		public function deletePublic(string $publicPath): AssetsCollectionContract {

			$this->publicStorage()->delete($this->publicPath($publicPath));

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function withdraw(string $path): AssetsCollectionContract {

			foreach ($this->buildPaths($path) as $curr) {
				$this->publicStorage()->delete($curr);
			}

			return $this;
		}


		/**
		 * @inheritDoc
		 */
		public function publish(string $path, bool $force = true): AssetsCollectionContract {

			$storagePath = $this->storagePath($path);

			// get the target paths for all builds
			$buildPaths = $this->buildPaths($path);
			
			$buildCache = [];

			foreach ($this->outputBuilders() as $buildName => $builders) {

				$currPath = $buildPaths[$buildName];

				if ($force || !$this->publicStorage()->exists($currPath)) {

					// build the asset
					$asset = $this->buildAsset($this->storage()->readStream($storagePath), $buildName, $buildCache);
					
					// store built asset
					$this->publicStorage()->put($currPath, $asset->asResource(), $asset->getStorageOptions());
				}
			}


			return $this;
		}
		
		

		/**
		 * Builds a new asset
		 * @param resource $source The original source
		 * @param string $buildName The build name
		 * @param Asset[] $buildCache Cache with virtual builds
		 * @return Asset The asset
		 */
		protected function buildAsset($source, string $buildName, array &$buildCache): Asset {

			$sp = explode(':', ltrim($buildName, ':'), 2);
			if (count($sp) == 2) {
				// this build uses a virtual build as source
				
				$virtualBuildName = $sp[1];
				
				// perform virtual build if not existing yet
				if (!($buildCache[$virtualBuildName] ?? null))
					$buildCache[$virtualBuildName] = $this->buildAsset($source, ":{$virtualBuildName}", $buildCache);

				$asset = $buildCache[$virtualBuildName];
				
				//$buildName = substr($buildName, 0, - strlen($virtualBuildName) - 1);
				
			} else {
				// this build uses the source file as source
				
				$asset = new ResourceAsset($source, [], $this->defaultStorageOptions());
			}
			
			/** @var AssetBuilder $lastBuilder */
			$lastBuilder = null;

			$builders = $this->builders()[$buildName];
			
			try {
				// process
				foreach ($builders as [$builder, $options]) {
					/** @var AssetBuilder $builder */    
					/** @var array $options */

					// build
					$asset = $builder->build($asset, $options);

					// cleanup the previous builder
					if ($lastBuilder)
						$lastBuilder->cleanup();
					
					$lastBuilder = $builder;
				}

				return $asset;
			}
			finally {

				// invoke cleanup for the last builder
				if ($lastBuilder)
					$lastBuilder->cleanup();
			}

		}

		/**
		 * Returns the default storage options for assets
		 * @return array The default storage options
		 */
		protected function defaultStorageOptions(): array {
			return [
				'visibility' => 'public',
			];	
		}		

		/**
		 * Gets the assets' path for all builds
		 * @param string $path The path
		 * @return string[] The paths for all builds
		 */
		protected function buildPaths(string $path): array {

			$ret = [];

			foreach ($this->outputBuilders() as $buildName => $builders) {

				$currPath = $this->prefixPath(Str::before($buildName, ':'), $path);

				// process path
				foreach ($builders as [$builder, $options]) {
					/** @var AssetBuilder $builder */
					/** @var array $options */

					$builder->processPath($currPath, $options);
				}

				$ret[$buildName] = $this->publicPath($currPath);
			}

			return $ret;
		}


		/**
		 * Returns the storage path for the given relative path
		 * @param string $path The path
		 * @return string The absolute storage path
		 */
		protected function storagePath(string $path): string {

			return $this->prefixPath($this->storagePath, $path);
		}

		/**
		 * Returns the public storage path for the given relative path
		 * @param string $path The path
		 * @return string The absolute public storage path
		 */
		protected function publicPath(string $path): string {

			return $this->prefixPath($this->publicPath, $path);
		}

		/**
		 * Adds the given prefix to the path
		 * @param string|null $prefix The prefix
		 * @param string $path The path
		 * @return string The prefixed path
		 */
		protected function prefixPath(?string $prefix, string $path): string {

			$path = ltrim($path, '/');

			return trim($prefix) !== '' ?
				"{$prefix}/{$path}" :
				$path;
		}

		/**
		 * Gets the storage disk instance
		 * @return Filesystem The storage disk instance
		 */
		protected function storage(): Filesystem {

			if (!($this->storage instanceof Filesystem))
				$this->storage = Storage::disk($this->storageName);

			return $this->storage;
		}

		/**
		 * Gets the public storage disk instance
		 * @return Filesystem The public storage disk instance
		 */
		protected function publicStorage(): Filesystem {

			if (!($this->publicStorage instanceof Filesystem))
				$this->publicStorage = Storage::disk($this->publicStorageName);

			return $this->publicStorage;
		}

		/**
		 * Returns all builders except virtual
		 * @return array
		 */
		protected function outputBuilders() {
			$ret = [];

			foreach ($this->builders() as $buildName => $curr) {
				if (!$this->isVirtualBuild($buildName))
					$ret[$buildName] = $curr;
			}

			return $ret;
		}

		/**
		 * Returns if the given build is a virtual build
		 * @param string $build The build name
		 * @return bool True if virtual. Else false.
		 */
		protected function isVirtualBuild(string $build): bool {
			return substr($build, 0, 1) == ':';
		}
		
		/**
		 * Gets the asset builders
		 * @return array The builders
		 */
		protected function builders(): array {

			if (!is_array($this->builders)) {

				$configuredBuilders = $this->config['build'] ?? [];

				// default with a plain copy build (user can disable it with ['_' => false]
				if (!array_key_exists('_', $configuredBuilders))
					$configuredBuilders['_'] = [];


				$builders = [];
				foreach ($configuredBuilders as $buildName => $currConfiguredBuilders) {

					if ($currConfiguredBuilders === false)
						continue;

					$builders[$buildName] = [];

					foreach ($currConfiguredBuilders as $key => $currBuilder) {

						if (is_int($key)) {
							// builder and options are specified as string
							$currArgs    = explode(':', $currBuilder, 2);
							$builderName = $currArgs[0];
							$builderArgs = ($currArgs[1] ?? null) !== null ? array_map('trim', explode(',', $currArgs[1])) : [];
						}
						else {
							// Arguments are passed as array. Builder name is the array key. 
							$builderName = $key;
							$builderArgs = (array)$currBuilder;
						}

						$builderInstance = $this->manager->builder($builderName);
						if (!$builderInstance)
							throw new RuntimeException("Asset builder with name \"{$builderName}\" is not registered.");

						$builders[$buildName][] = [$builderInstance, $builderArgs];
					}


				}

				$this->builders = $builders;
			}

			return $this->builders;
		}

		/**
		 * Gets a virus scanner instance
		 * @return VirusScanner The virus scanner instance
		 */
		protected function virusScan(): VirusScanner {

			if (!$this->virusScan)
				$this->virusScan = app(VirusScanner::class);

			return $this->virusScan;
		}

	}