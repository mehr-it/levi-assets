<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Builder\ContentEncodingBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentEncodingBuilderTest extends TestCase
	{
		public function testBuild() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentEncodingBuilder();

			$writeOptions = [];
			$options      = ['gzip'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Encoding' => 'gzip'], $writeOptions);

		}

		public function testBuild_multipleDirectives() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentEncodingBuilder();

			$writeOptions = [];
			$options      = ['gzip', 'deflate'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Encoding' => 'gzip, deflate'], $writeOptions);

		}

		public function testBuild_noOptions() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentEncodingBuilder();

			$writeOptions = [];
			$options      = [];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame([], $writeOptions);

		}
	}