<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Facades;


	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Facades\Assets;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class AssetsTest extends TestCase
	{
		public function testAncestorCall() {
			// mock ancestor
			$mock = $this->mockAppSingleton(AssetsManager::class, AssetsManager::class);
			$mock->expects($this->once())
				->method('registerCollection')
				->with('c1', []);

			Assets::registerCollection('c1', []);
		}
	}