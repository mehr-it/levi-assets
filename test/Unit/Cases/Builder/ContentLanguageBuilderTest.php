<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Builder\ContentLanguageBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentLanguageBuilderTest extends TestCase
	{
		public function testBuild() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentLanguageBuilder();

			$writeOptions = [];
			$options      = ['de-DE'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Language' => 'de-DE'], $writeOptions);

		}

		public function testBuild_multipleDirectives() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentLanguageBuilder();

			$writeOptions = [];
			$options      = ['de-DE', 'en-CA'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Language' => 'de-DE, en-CA'], $writeOptions);

		}

		public function testBuild_noOptions() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentLanguageBuilder();

			$writeOptions = [];
			$options      = [];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame([], $writeOptions);

		}

	}