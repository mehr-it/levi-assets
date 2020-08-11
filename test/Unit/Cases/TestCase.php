<?php


	namespace MehrItLeviAssetsTest\Unit\Cases;


	use MehrIt\LeviAssets\Facades\Assets;
	use MehrIt\LeviAssets\Provider\LeviAssetsServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		protected function getPackageProviders($app) {

			return [
				LeviAssetsServiceProvider::class,
			];

		}

		/**
		 * @inheritDoc
		 */
		protected function getPackageAliases($app) {

			return [
				'Assets' => Assets::class,
			];
		}


		/**
		 * @param $abstract
		 * @param null $class
		 * @return \PHPUnit\Framework\MockObject\MockObject
		 */
		protected function mockAppSingleton($abstract, $class = null) {

			if ($class === null)
				$class = $abstract;

			$mock = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();

			app()->singleton($abstract, function() use ($mock) {
				return $mock;
			});

			return $mock;
		}
	}