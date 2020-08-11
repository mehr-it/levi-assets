<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Builder\ContentDispositionBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentDispositionBuilderTest extends TestCase
	{
		public function testBuild() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentDispositionBuilder();

			$writeOptions = [];
			$options      = ['attachment'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Disposition' => 'attachment'], $writeOptions);

		}

		public function testBuild_multipleDirectives() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentDispositionBuilder();

			$writeOptions = [];
			$options      = ['attachment', 'filename="test.jpg"'];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Disposition' => 'attachment; filename="test.jpg"'], $writeOptions);

		}

		public function testBuild_noOptions() {

			$res = fopen('php://temp', 'w+');

			$builder = new ContentDispositionBuilder();

			$writeOptions = [];
			$options      = [];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame([], $writeOptions);

		}
	}