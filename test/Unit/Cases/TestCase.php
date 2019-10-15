<?php


	namespace MehrItLeviAssetsTest\Unit\Cases;


	use MehrIt\LeviAssets\Provider\LeviAssetsServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		protected function getPackageProviders($app) {

			return [
				LeviAssetsServiceProvider::class,
			];

		}
	}