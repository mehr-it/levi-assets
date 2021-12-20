<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use Carbon\Carbon;
	use MehrIt\LeviAssets\Asset\ResourceAsset;
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

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ExpiresBuilder();
			
			$options      = [Carbon::now()->getTimestamp()];

			$builder->build($res, $options);
			
			$this->assertSame(Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T'), $res->getMeta('Expires'));

		}

		public function testBuild_fromDate() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ExpiresBuilder();
			
			$options      = [Carbon::now()];

			$builder->build($res, $options);

			$this->assertSame(Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T'), $res->getMeta('Expires'));

		}

		public function testBuild_fromString() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ExpiresBuilder();
			
			$options      = [Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T')];

			$builder->build($res, $options);

			$this->assertSame(Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T'), $res->getMeta('Expires'));

		}


		public function testBuild_noOptions() {

			$res = new ResourceAsset(fopen('php://temp', 'w+'), [], []);

			$builder = new ExpiresBuilder();
			
			$options      = [];

			$builder->build($res, $options);

			$this->assertSame(null, $res->getMeta('Expires'));

		}
	}