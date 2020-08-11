<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Builder\CacheControlBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class CacheControlBuilderTest extends TestCase
	{

		public function testBuild() {

			$res = fopen('php://temp', 'w+');

			$builder = new CacheControlBuilder();

			$writeOptions = [];
			$options      = ['max-age=86400'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Cache-Control' => 'max-age=86400'], $writeOptions);

		}

		public function testBuild_multipleDirectives() {

			$res = fopen('php://temp', 'w+');

			$builder = new CacheControlBuilder();

			$writeOptions = [];
			$options      = ['max-age=86400', 'public', 'immutable'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Cache-Control' => 'max-age=86400, public, immutable'], $writeOptions);

		}

		public function testBuild_noOptions() {

			$res = fopen('php://temp', 'w+');

			$builder = new CacheControlBuilder();

			$writeOptions = [];
			$options      = [];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame([], $writeOptions);

		}

	}