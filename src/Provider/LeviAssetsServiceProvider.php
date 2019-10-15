<?php


	namespace MehrIt\LeviAssets\Provider;


	use Illuminate\Support\ServiceProvider;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;

	class LeviAssetsServiceProvider extends ServiceProvider
	{
		public $defer = true;


		public function boot() {

			$this->publishes([
				__DIR__ . '/../../config/leviAssets.php' => $this->app->configPath('leviAssets.php'),
			], 'config');

		}

		public function register() {

			$this->mergeConfigFrom(__DIR__ . '/../../config/leviAssets.php', 'leviAssets');

			$this->app->singleton(VirusScanner::class, function() {
				return new VirusScanner(
					$this->app['config']['leviAssets.virusScan.socket'],
					$this->app['config']['leviAssets.virusScan.timeout'],
					$this->app['config']['leviAssets.virusScan.bypass']
				);
			});
		}

		/**
		 * Get the services provided by the provider.
		 *
		 * @return array
		 */
		public function provides() {
			return [
				VirusScanner::class,
			];
		}
	}