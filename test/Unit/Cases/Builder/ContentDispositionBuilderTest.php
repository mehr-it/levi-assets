<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\ContentDispositionBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentDispositionBuilderTest extends TestCase
	{
		public function testBuild() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentDispositionBuilder();
			
			$options      = ['attachment'];

			$builder->build($res, $options);

			$this->assertSame('attachment', $res->getMeta('Content-Disposition'));

		}

		public function testBuild_multipleDirectives() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentDispositionBuilder();
			
			$options      = ['attachment', 'filename="test.jpg"'];

			$builder->build($res, $options);

			$this->assertSame('attachment; filename="test.jpg"', $res->getMeta('Content-Disposition'));

		}

		public function testBuild_noOptions() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentDispositionBuilder();
			
			$options      = [];

			$builder->build($res, $options);

			$this->assertSame(null, $res->getMeta('Content-Disposition'));

		}
	}