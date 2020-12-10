<?php


	namespace MehrIt\LeviAssets\Provider;


	use Illuminate\Contracts\Support\DeferrableProvider;
	use Illuminate\Support\Arr;
	use Illuminate\Support\ServiceProvider;
	use Illuminate\View\Compilers\BladeCompiler;
	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Contracts\AssetsCollection;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;

	class LeviAssetsServiceProvider extends ServiceProvider implements DeferrableProvider
	{
		public $defer = true;


		public function boot() {

			$this->publishes([
				__DIR__ . '/../../config/leviAssets.php' => $this->app->configPath('leviAssets.php'),
			], 'config');

		}

		/** @noinspection PhpUnusedParameterInspection */
		public function register() {

			$this->mergeConfigFrom(__DIR__ . '/../../config/leviAssets.php', 'leviAssets');

			$this->app->singleton(AssetsManager::class, function() {
				$manager =  new AssetsManager(
					$this->app['config']['leviAssets.links'] ?? []
				);

				foreach($this->app['config']['leviAssets.collections'] as $name => $config) {
					$manager->registerCollection($name, Arr::wrap($config));
				}
				foreach($this->app['config']['leviAssets.builders'] as $name => $cls) {
					$manager->registerBuilder($name, $cls);
				}

				return $manager;
			});

			$this->app->singleton(VirusScanner::class, function() {
				return new VirusScanner(
					$this->app['config']['leviAssets.virusScan.socket'],
					$this->app['config']['leviAssets.virusScan.timeout'],
					$this->app['config']['leviAssets.virusScan.bypass']
				);
			});

			$this->app->bind(AssetsCollection::class, function($a, $params) {
				return new \MehrIt\LeviAssets\Collection\AssetsCollection(
					$params['config'],
					$params['manager']
				);
			});

			$this->app->extend('blade.compiler', function($compiler) {
				/** @var BladeCompiler $compiler */
				$compiler->directive('assetLink', function($expression) {
					return "<?php echo \Assets::link({$expression}); ?>";
				});

				return $compiler;
			});
		}

		/**
		 * Get the services provided by the provider.
		 *
		 * @return array
		 */
		public function provides() {
			return [
				AssetsCollection::class,
				AssetsManager::class,
				VirusScanner::class,
				'blade.compiler',
			];
		}
	}