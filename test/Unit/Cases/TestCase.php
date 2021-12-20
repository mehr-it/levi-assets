<?php


	namespace MehrItLeviAssetsTest\Unit\Cases;


	use MehrIt\LeviAssets\Facades\Assets;
	use MehrIt\LeviAssets\Provider\LeviAssetsServiceProvider;
	use MehrIt\LeviImages\Facades\LeviImages;
	use MehrIt\LeviImages\Provider\LeviImagesServiceProvider;

	class TestCase extends \Orchestra\Testbench\TestCase
	{
		protected function getPackageProviders($app) {

			return [
				LeviAssetsServiceProvider::class,
				LeviImagesServiceProvider::class,
			];

		}

		/**
		 * @inheritDoc
		 */
		protected function getPackageAliases($app) {

			return [
				'Assets' => Assets::class,
				'LeviImages' => LeviImages::class,
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
		
		protected function resourceWithContent(string $content) {
			$f = fopen('php://memory', 'w+');
			fwrite($f, $content);
			
			rewind($f);
			
			return $f;
		}
		
		protected function png1Pix() {
			return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
		}
		
		protected function png10Pix() {
			return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAQAAAAnOwc2AAAAD0lEQVR42mNkwAIYh7IgAAVVAAuInjI5AAAAAElFTkSuQmCC');
		}
	}