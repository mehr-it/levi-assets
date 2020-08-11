<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use Carbon\Carbon;
	use MehrIt\LeviAssets\Builder\ExpiresBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ExpiresBuilderTest extends TestCase
	{
		/**
		 * @inheritDoc
		 */
		protected function setUp(): void {
			parent::setUp();

			Carbon::setTestNow(Carbon::now());
		}


		public function testBuild_fromTimestamp() {

			$res = fopen('php://temp', 'w+');

			$builder = new ExpiresBuilder();

			$writeOptions = [];
			$options      = [Carbon::now()->getTimestamp()];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Expires' => Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T')], $writeOptions);

		}

		public function testBuild_fromDate() {

			$res = fopen('php://temp', 'w+');

			$builder = new ExpiresBuilder();

			$writeOptions = [];
			$options      = [Carbon::now()];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Expires' => Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T')], $writeOptions);

		}

		public function testBuild_fromString() {

			$res = fopen('php://temp', 'w+');

			$builder = new ExpiresBuilder();

			$writeOptions = [];
			$options      = [Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T')];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(['Expires' => Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T')], $writeOptions);

		}


		public function testBuild_noOptions() {

			$res = fopen('php://temp', 'w+');

			$builder = new ExpiresBuilder();

			$writeOptions = [];
			$options      = [];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame([], $writeOptions);

		}
	}