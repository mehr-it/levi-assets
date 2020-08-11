<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Provider;


	use Illuminate\Support\Facades\Config;
	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Contracts\AssetBuilder;
	use MehrIt\LeviAssets\Contracts\AssetsCollection;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class LeviAssetsServiceProviderTest extends TestCase
	{

		public function testVirusScannerRegistration_withDefaultConfig() {

			/** @var VirusScanner $resolved */
			$resolved = app(VirusScanner::class);

			$this->assertInstanceOf(VirusScanner::class, $resolved);
			$this->assertSame($resolved, app(VirusScanner::class));

			$this->assertSame('unix:///var/run/clamav/clamd.ctl', $resolved->getSocket());
			$this->assertSame(30, $resolved->getTimeout());
			$this->assertSame(false, $resolved->isBypass());

		}

		public function testVirusScannerRegistration_withModifiedConfig() {

			config()->set('leviAssets.virusScan.socket', 'unix:///an/other/socket');
			config()->set('leviAssets.virusScan.timeout', '15');
			config()->set('leviAssets.virusScan.bypass', 'true');

			/** @var VirusScanner $resolved */
			$resolved = app(VirusScanner::class);


			$this->assertInstanceOf(VirusScanner::class, $resolved);
			$this->assertSame($resolved, app(VirusScanner::class));

			$this->assertSame('unix:///an/other/socket', $resolved->getSocket());
			$this->assertSame(15, $resolved->getTimeout());
			$this->assertSame(true, $resolved->isBypass());

		}

		public function testAssetManagerRegistration() {

			$b1 = $this->getMockBuilder(AssetBuilder::class)->getMock();
			$b2 = $this->getMockBuilder(AssetBuilder::class)->getMock();

			Config::set('leviAssets.collections', [
				'c1' => [
					'storage_path' => 'path/to/1'
				],
				'c2' => [
					'storage_path' => 'path/to/2'
				]
			]);

			Config::set('leviAssets.builders', [
				'b1' => 'builder1',
				'b2' => 'builder2',
			]);

			app()->bind('builder1', function() use ($b1) {
				return $b1;
			});
			app()->bind('builder2', function() use ($b2) {
				return $b2;
			});

			$manager = app(AssetsManager::class);


			$this->assertSame(
				[
					'storage_path' => 'path/to/1'
				],
				$manager->collection('c1')->getConfig()
			);
			$this->assertSame(
				[
					'storage_path' => 'path/to/2'
				],
				$manager->collection('c2')->getConfig()
			);

			$this->assertSame($b1, $manager->builder('b1'));
			$this->assertSame($b2, $manager->builder('b2'));

		}

		public function testAssetLinkDirective() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path/1.jpg')
				->willReturn([
					'_'  => 'public/file/1.jpg',
				]);

			app()->bind(AssetsCollection::class, function () use ($collectionMock) {
				return $collectionMock;
			});

			app(AssetsManager::class)->registerCollection('c1', []);


			$blade = resolve('blade.compiler');


			$this->assertDirectiveOutput(
				$blade,
				'https://localhost/public/file/1.jpg',
				'@assetLink("c1", "my/path/1.jpg")'
			);
		}


		/**
		 * Evaluate a Blade expression with the given $variables in scope.
		 *
		 * @param string $expected The expected output.
		 * @param string $expression The Blade directive, as it would be written in a view.
		 * @param array $variables Variables to extract() into the scope of the eval() statement.
		 * @param string $message A message to display if the output does not match $expected.
		 */
		protected function assertDirectiveOutput(
			$blade,
			string $expected,
			string $expression = '',
			array $variables = [],
			string $message = ''
		) {
			$compiled = $blade->compileString($expression);

			/*
			 * Normally using eval() would be a big no-no, but when you're working on a templating
			 * engine it's difficult to avoid.
			 */
			ob_start();
			extract($variables);
			eval(' ?>' . $compiled . '<?php ');
			$output = ob_get_clean();

			$this->assertEquals($expected, $output, $message);
		}
	}