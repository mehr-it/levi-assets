<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\ContentEncodingBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentEncodingBuilderTest extends TestCase
	{
		public function testBuild() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentEncodingBuilder();
			
			$options      = ['gzip'];

			$builder->build($res, $options);
			
			$this->assertSame('gzip', $res->getMeta('Content-Encoding'));
		}

		public function testBuild_multipleDirectives() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentEncodingBuilder();
			
			$options      = ['gzip', 'deflate'];

			$builder->build($res, $options);

			$this->assertSame('gzip, deflate', $res->getMeta('Content-Encoding'));

		}

		public function testBuild_noOptions() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentEncodingBuilder();
			
			$options      = [];

			$builder->build($res, $options);

			$this->assertSame(null, $res->getMeta('Content-Encoding'));

		}
	}