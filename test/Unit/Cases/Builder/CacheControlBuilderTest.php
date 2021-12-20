<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\CacheControlBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class CacheControlBuilderTest extends TestCase
	{

		public function testBuild() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new CacheControlBuilder();

			$options = ['max-age=86400'];

			$builder->build($res, $options);

			$this->assertSame('max-age=86400', $res->getMeta('Cache-Control'));

		}

		public function testBuild_multipleDirectives() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new CacheControlBuilder();
			
			$options      = ['max-age=86400', 'public', 'immutable'];

			$builder->build($res, $options);
			
			$this->assertSame('max-age=86400, public, immutable', $res->getMeta('Cache-Control'));
		}

		public function testBuild_noOptions() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new CacheControlBuilder();
			
			$options      = [];

			$builder->build($res, $options);

			$this->assertSame(null, $res->getMeta('Cache-Control'));

		}

	}