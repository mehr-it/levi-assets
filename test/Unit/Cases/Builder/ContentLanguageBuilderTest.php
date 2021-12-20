<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\ContentLanguageBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentLanguageBuilderTest extends TestCase
	{
		public function testBuild() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentLanguageBuilder();
			
			$options      = ['de-DE'];

			$builder->build($res,  $options);
			
			$this->assertSame('de-DE', $res->getMeta('Content-Language'));

		}

		public function testBuild_multipleDirectives() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentLanguageBuilder();
			
			$options      = ['de-DE', 'en-CA'];

			$builder->build($res, $options);

			$this->assertSame('de-DE, en-CA', $res->getMeta('Content-Language'));
			

		}

		public function testBuild_noOptions() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ContentLanguageBuilder();
			
			$options      = [];

			$builder->build($res, $options);

			$this->assertSame(null, $res->getMeta('Content-Language'));

		}

	}