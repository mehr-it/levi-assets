<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Collection;


	use Illuminate\Support\Facades\Storage;
	use League\Flysystem\Config;
	use League\Flysystem\Filesystem;
	use League\Flysystem\Memory\MemoryAdapter;
	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Collection\AssetsCollection;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use MehrItLeviAssetsTest\Helpers\MocksAssetBuilder;
	use MehrItLeviAssetsTest\Helpers\TestsWithFiles;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class AssetsCollectionTest extends TestCase
	{
		use MocksAssetBuilder;
		use TestsWithFiles;

		/**
		 * @var
		 */
		protected $fileSystem;

		/**
		 * @inheritDoc
		 */
		protected function getEnvironmentSetUp($app) {

			$app['config']->set('filesystems.disks.testing.driver', 'memory');
			$app['config']->set('filesystems.disks.testing_public.driver', 'memory');
			$app['config']->set('filesystems.default', 'testing');

			parent::getEnvironmentSetUp($app);
		}

		/**
		 * @inheritDoc
		 */
		protected function setUp(): void {
			parent::setUp();

			$this->fileSystem = new Filesystem(new TestingMemoryAdapter());

			Storage::extend('memory', function() {
				return $this->fileSystem;
			});
		}


		public function testGetConfig() {


			$config = [
				'storage_path' => 'my/path',
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($config, $collection->getConfig());

		}
		
		public function testGetPublicStorageName() {


			$config = [
				'storage_path' => 'my/path',
				'public_storage' => 'public_disk'
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame('public_disk', $collection->getPublicStorageName());
		}
		
		public function testGetStorageName() {


			$config = [
				'storage_path' => 'my/path',
				'storage' => 'private_disk'
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame('private_disk', $collection->getStorageName());
		}


		public function testLinkFilters() {


			$config = [
				'link_filters' => [
					'filter1:arg1,arg2, arg3',
					'filter2',
					'filter3:1 ',
				],
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame(
				[
					['filter1', 'arg1', 'arg2', 'arg3'],
					['filter2'],
					['filter3', '1'],
				],
				$collection->linkFilters()
			);

			// retry with cached version => this should of course be the same
			$this->assertSame(
				[
					['filter1', 'arg1', 'arg2', 'arg3'],
					['filter2'],
					['filter3', '1'],
				],
				$collection->linkFilters()
			);
		}

		public function testResolve() {

			$config = [
				'public_path' => 'images',
				'build' => [
					'small'  => [
						'jpg'
					],
					'medium' => [],
					'large'  => [
						'size:50,90',
						'jpg'
					],
				],
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();
			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							null,
							function(&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							null,
							function(&$path, $options) {
								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options[0]}x{$options[1]}.$1", $path);
							}
						),
					],
				]);


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame(
				[
					'small'  => 'images/small/my/image.jpg',
					'medium' => 'images/medium/my/image.png',
					'large'  => 'images/large/my/image_50x90.jpg',
					'_'      => 'images/_/my/image.png',
				],
				$collection->resolve('my/image.png')
			);

			// try again with builders read from cache
			$this->assertSame(
				[
					'small'  => 'images/small/my/image.jpg',
					'medium' => 'images/medium/my/image.png',
					'large'  => 'images/large/my/image_50x90.jpg',
					'_'      => 'images/_/my/image.png',
				],
				$collection->resolve('my/image.png')
			);
		}

		public function testResolve_withoutDefaultBuilder() {

			$config = [
				'public_path' => 'images',
				'build' => [
					'_'      => false,
					'small'  => [
						'jpg'
					],
					'medium' => [],
					'large'  => [
						'size:50,90',
						'jpg'
					],
				],
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();
			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							null,
							function(&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							null,
							function(&$path, $options) {
								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options[0]}x{$options[1]}.$1", $path);
							}
						),
					],
				]);


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame(
				[
					'small'  => 'images/small/my/image.jpg',
					'medium' => 'images/medium/my/image.png',
					'large'  => 'images/large/my/image_50x90.jpg',
				],
				$collection->resolve('my/image.png')
			);

			// try again with builders read from cache
			$this->assertSame(
				[
					'small'  => 'images/small/my/image.jpg',
					'medium' => 'images/medium/my/image.png',
					'large'  => 'images/large/my/image_50x90.jpg',
				],
				$collection->resolve('my/image.png')
			);
		}

		public function testPut() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));


			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame('private', Storage::disk('testing')->getVisibility('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame('public', Storage::disk('testing_public')->getVisibility('public_root/_/the/test/file'));

		}

		public function testPut_defaultStorageWithoutPrefixes() {


			$content = 'file content 1';

			$config = [
				'public_storage' => 'testing_public',
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));


			$this->assertSame($content, Storage::disk('testing')->get('the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('_/the/test/file'));

		}

		public function testPut_noVirusScan() {


			$content = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res = $this->resourceWithContent($content);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));
			$this->assertSame($collection, $collection->put('the/test/file2', $res2));


			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2'));
			$this->assertSame($content2, Storage::disk('testing_public')->get('public_root/_/the/test/file2'));

		}

		public function testPut_customBuilders() {


			$content1 = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
				'build' => [
					'fmt_jpeg' => [
						'jpg',
					],
					'fmt_jpeg_small' => [
						'size:50,90',
						'jpg',
					]
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res1 = $this->resourceWithContent($content1);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							function(Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, '||jpg');

								rewind($res);

								$asset->setStorageOption('Meta1', 'value1');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);
								
								fwrite($res, "||{$options[0]}x{$options[1]}");

								rewind($res);
								
								$asset->setStorageOption('Meta2', 'value2');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path, $options) {
								
								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options[0]}x{$options[1]}.$1", $path);
							}
						),
					],
				]);


			$this->assertSame($collection, $collection->put('the/test/file.png', $res1));
			$this->assertSame($collection, $collection->put('the/test/file2.png', $res2));


			$this->assertSame($content1, Storage::disk('testing')->get('my/path/the/test/file.png'));
			$this->assertSame($content1, Storage::disk('testing_public')->get('public_root/_/the/test/file.png'));
			$this->assertSame("{$content1}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file.jpg'));
			$this->assertSame("{$content1}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file_50x90.jpg'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2.png'));
			$this->assertSame($content2, Storage::disk('testing_public')->get('public_root/_/the/test/file2.png'));
			$this->assertSame("{$content2}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file2.jpg'));
			$this->assertSame("{$content2}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file2_50x90.jpg'));

			$this->assertSame('value1', Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg/the/test/file2.jpg')->get('Meta1'));
			$this->assertSame(null, Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg/the/test/file2.jpg')->get('Meta2'));
			$this->assertSame('value1', Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg_small/the/test/file_50x90.jpg')->get('Meta1'));
			$this->assertSame('value2', Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg_small/the/test/file_50x90.jpg')->get('Meta2'));

		}

		public function testPut_customBuilders_noDefaultBuilder() {


			$content1 = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
				'build' => [
					'_'              => false,
					'fmt_jpeg'       => [
						'jpg',
					],
					'fmt_jpeg_small' => [
						'size:50,90',
						'jpg',
					]
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res1 = $this->resourceWithContent($content1);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, '||jpg');

								rewind($res);

								$asset->setStorageOption('Meta1', 'value1');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, "||{$options[0]}x{$options[1]}");

								rewind($res);

								$asset->setStorageOption('Meta2', 'value2');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path, $options) {

								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options[0]}x{$options[1]}.$1", $path);
							}
						),
					],
				]);


			$this->assertSame($collection, $collection->put('the/test/file.png', $res1));
			$this->assertSame($collection, $collection->put('the/test/file2.png', $res2));


			$this->assertSame($content1, Storage::disk('testing')->get('my/path/the/test/file.png'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file.png'));
			$this->assertSame("{$content1}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file.jpg'));
			$this->assertSame("{$content1}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file_50x90.jpg'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2.png'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file2.png'));
			$this->assertSame("{$content2}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file2.jpg'));
			$this->assertSame("{$content2}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file2_50x90.jpg'));


		}

		public function testPut_customBuilders_assocOptions() {


			$content1 = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
				'build'          => [
					'fmt_jpeg'       => [
						'jpg',
					],
					'fmt_jpeg_small' => [
						'size' => ['width' => '50', 'height' => '90'],
						'jpg',
					]
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res1 = $this->resourceWithContent($content1);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, '||jpg');

								rewind($res);

								$asset->setStorageOption('Meta1', 'value1');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, "||{$options['width']}x{$options['height']}");

								rewind($res);

								$asset->setStorageOption('Meta2', 'value2');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path, $options) {

								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options['width']}x{$options['height']}.$1", $path);
							}
						),
					],
				]);


			$this->assertSame($collection, $collection->put('the/test/file.png', $res1));
			$this->assertSame($collection, $collection->put('the/test/file2.png', $res2));


			$this->assertSame($content1, Storage::disk('testing')->get('my/path/the/test/file.png'));
			$this->assertSame($content1, Storage::disk('testing_public')->get('public_root/_/the/test/file.png'));
			$this->assertSame("{$content1}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file.jpg'));
			$this->assertSame("{$content1}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file_50x90.jpg'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2.png'));
			$this->assertSame($content2, Storage::disk('testing_public')->get('public_root/_/the/test/file2.png'));
			$this->assertSame("{$content2}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file2.jpg'));
			$this->assertSame("{$content2}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file2_50x90.jpg'));

			$this->assertSame('value1', Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg/the/test/file2.jpg')->get('Meta1'));
			$this->assertSame(null, Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg/the/test/file2.jpg')->get('Meta2'));
			$this->assertSame('value1', Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg_small/the/test/file_50x90.jpg')->get('Meta1'));
			$this->assertSame('value2', Storage::disk('testing_public')->getDriver()->getAdapter()->getPassedConfig('public_root/fmt_jpeg_small/the/test/file_50x90.jpg')->get('Meta2'));

		}

		public function testPut_customBuilders_virtualBuilds() {


			$content1 = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
				'build'          => [
					':v1' => [
						'b1',	
					],
					'r1:v1'       => [
						'b2',
					],
					'r2:v1' => [
						'b3',
					]
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res1 = $this->resourceWithContent($content1);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$virtualCallCount = 0;
			
			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'b1',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) use (&$virtualCallCount) {

								$this->assertSame(0, $virtualCallCount);
								++$virtualCallCount;
								
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, 'a');

								rewind($res);

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							}
						),
					],
					[
						'b2',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, "b2");

								rewind($res);
								

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							}
						),
					],
					[
						'b3',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, "b3");

								rewind($res);
								

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							}
						),
					],
				]);


			$this->assertSame($collection, $collection->put('the/test/file.png', $res1));



			$this->assertSame($content1, Storage::disk('testing')->get('my/path/the/test/file.png'));
			$this->assertSame($content1, Storage::disk('testing_public')->get('public_root/_/the/test/file.png'));
			$this->assertSame("{$content1}ab2", Storage::disk('testing_public')->get('public_root/r1/the/test/file.png'));
			$this->assertSame("{$content1}ab3", Storage::disk('testing_public')->get('public_root/r2/the/test/file.png'));
			

		}


		public function testPut_noPublish() {


			$content  = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res  = $this->resourceWithContent($content);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res, false));
			$this->assertSame($collection, $collection->put('the/test/file2', $res2, false));


			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file2'));

		}

		public function testPut_overwriteExisting() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function ($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			Storage::disk('testing')->put('the/test/file', 'existing content');
			Storage::disk('testing_public')->put('public_root/_/the/test/file', 'existing content');


			$this->assertSame($collection, $collection->put('the/test/file', $res));

			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/other/the/test/file'));

		}

		public function testPut_overwriteExistingPublicOnly() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function ($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			Storage::disk('testing_public')->put('public_root/_/the/test/file', 'existing content');
			Storage::disk('testing_public')->put('public_root/other/the/test/file', 'existing content');


			$this->assertSame($collection, $collection->put('the/test/file', $res));

			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/other/the/test/file'));

		}

		public function testPutAndDelete() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function ($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));

			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/other/the/test/file'));

			$this->assertSame($collection, $collection->delete('the/test/file'));

			$this->assertSame(false, Storage::disk('testing')->exists('my/path/the/test/file'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/other/the/test/file'));

		}

		public function testPutAndDelete_notAllExistingAnymore() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function ($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));

			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/other/the/test/file'));

			Storage::disk('testing')->delete('my/path/the/test/file');
			Storage::disk('testing_public')->delete('public_root/other/the/test/file');

			$this->assertSame($collection, $collection->delete('the/test/file'));

			$this->assertSame(false, Storage::disk('testing')->exists('my/path/the/test/file'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/other/the/test/file'));

		}


		public function testPutAndGet() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function ($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));

			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/other/the/test/file'));

			$this->assertSame($content, \Safe\stream_get_contents($collection->get('the/test/file')));


		}

		public function testGet_notExisting() {

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();

			$collection = new AssetsCollection($config, $managerMock);


			$this->assertSame(null, $collection->get('the/test/file'));


		}

		public function testPutAndExists() {


			$content = 'file content 1';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->once())
				->method('scanStream')
				->willReturnCallback(function ($resource, $rewind) use ($scanner, $content) {

					$this->assertSame($content, \Safe\stream_get_contents($resource));

					if ($rewind)
						\Safe\rewind($resource);

					return $scanner;
				});

			$res = $this->resourceWithContent($content);

			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $res));

			$this->assertSame($content, Storage::disk('testing')->get('my/path/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/_/the/test/file'));
			$this->assertSame($content, Storage::disk('testing_public')->get('public_root/other/the/test/file'));

			$this->assertSame(true, $collection->exists('the/test/file'));


		}

		public function testExists_notExisting() {

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();

			$collection = new AssetsCollection($config, $managerMock);


			$this->assertSame(false, $collection->exists('the/test/file'));


		}

		public function testPutAndIterate() {


			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $this->resourceWithContent('a')));
			$this->assertSame($collection, $collection->put('the/test/file2', $this->resourceWithContent('b')));
			$this->assertSame($collection, $collection->put('the/file3', $this->resourceWithContent('c')));

			$sortedRet = iterator_to_array($collection->iterate());

			$this->assertSame(
				[
					'the/file3',
					'the/test/file',
					'the/test/file2',
				],
				$sortedRet
			);


		}

		public function testPutAndIteratePublic() {


			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $this->resourceWithContent('a')));
			$this->assertSame($collection, $collection->put('the/test/file2', $this->resourceWithContent('b')));
			$this->assertSame($collection, $collection->put('the/file3', $this->resourceWithContent('c')));


			$this->assertSame(
				[
					'public_root/_/the/file3',
					'public_root/_/the/test/file',
					'public_root/_/the/test/file2',
					'public_root/other/the/file3',
					'public_root/other/the/test/file',
					'public_root/other/the/test/file2',
				],
				Storage::disk('testing_public')->allFiles('public_root')
			);


		}

		public function testPutAndDeletePublic() {


			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $this->resourceWithContent('a')));
			$this->assertSame($collection, $collection->put('the/test/file2', $this->resourceWithContent('b')));
			$this->assertSame($collection, $collection->put('the/file3', $this->resourceWithContent('c')));


			$this->assertSame($collection, $collection->deletePublic('_/the/file3'));
			$this->assertSame($collection, $collection->deletePublic('other/the/test/file'));
			$this->assertSame($collection, $collection->deletePublic('notExisting/file'));

			$this->assertSame(
				[
					'public_root/_/the/test/file',
					'public_root/_/the/test/file2',
					'public_root/other/the/file3',
					'public_root/other/the/test/file2',
				],
				Storage::disk('testing_public')->allFiles('public_root')
			);


		}

		public function testPutAndWithdraw() {


			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $this->resourceWithContent('a')));
			$this->assertSame($collection, $collection->put('the/test/file2', $this->resourceWithContent('b')));
			$this->assertSame($collection, $collection->put('the/file3', $this->resourceWithContent('c')));


			$this->assertSame($collection, $collection->withdraw('the/test/file'));

			$this->assertSame(
				[
					'public_root/_/the/file3',
					'public_root/_/the/test/file2',
					'public_root/other/the/file3',
					'public_root/other/the/test/file2',
				],
				Storage::disk('testing_public')->allFiles('public_root')
			);


		}

		public function testPutAndWithdraw_someNotExisting() {


			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'build'          => [
					'other' => [],
				],
				'virus_scan'     => false,
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');


			$collection = new AssetsCollection($config, $managerMock);

			$this->assertSame($collection, $collection->put('the/test/file', $this->resourceWithContent('a')));
			$this->assertSame($collection, $collection->put('the/test/file2', $this->resourceWithContent('b')));
			$this->assertSame($collection, $collection->put('the/file3', $this->resourceWithContent('c')));


			$this->assertSame($collection, $collection->withdraw('the/test/file'));
			$this->assertSame($collection, $collection->withdraw('the/test/file2'));
			$this->assertSame($collection, $collection->withdraw('notExisting/file'));

			Storage::disk('testing_public')->delete('public_root/other/the/test/file2');

			$this->assertSame(
				[
					'public_root/_/the/file3',
					'public_root/other/the/file3',
				],
				Storage::disk('testing_public')->allFiles('public_root')
			);


		}


		public function testPutAndPublish_customBuilders_force() {


			$content1 = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
				'build'          => [
					'fmt_jpeg'       => [
						'jpg',
					],
					'fmt_jpeg_small' => [
						'size:50,90',
						'jpg',
					]
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res1 = $this->resourceWithContent($content1);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, '||jpg');

								rewind($res);

								$asset->setStorageOption('Meta1', 'value1');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, "||{$options[0]}x{$options[1]}");

								rewind($res);

								$asset->setStorageOption('Meta2', 'value2');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path, $options) {

								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options[0]}x{$options[1]}.$1", $path);
							}
						),
					],
				]);


			$this->assertSame($collection, $collection->put('the/test/file.png', $res1, false));
			$this->assertSame($collection, $collection->put('the/test/file2.png', $res2, false));


			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file.png'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file2.png'));


			Storage::disk('testing_public')->put('public_root/_/the/test/file.png', 'existingContent');


			$this->assertSame($collection, $collection->publish('the/test/file.png'));
			$this->assertSame($collection, $collection->publish('the/test/file2.png'));


			$this->assertSame($content1, Storage::disk('testing')->get('my/path/the/test/file.png'));
			$this->assertSame($content1, Storage::disk('testing_public')->get('public_root/_/the/test/file.png'));
			$this->assertSame("{$content1}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file.jpg'));
			$this->assertSame("{$content1}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file_50x90.jpg'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2.png'));
			$this->assertSame($content2, Storage::disk('testing_public')->get('public_root/_/the/test/file2.png'));
			$this->assertSame("{$content2}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file2.jpg'));
			$this->assertSame("{$content2}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file2_50x90.jpg'));


		}

		public function testPutAndPublish_customBuilders_noForce() {


			$content1 = 'file content 1';
			$content2 = 'file content 2';

			$config = [
				'storage'        => 'testing',
				'storage_path'   => 'my/path',
				'public_storage' => 'testing_public',
				'public_path'    => 'public_root',
				'virus_scan'     => false,
				'build'          => [
					'fmt_jpeg'       => [
						'jpg',
					],
					'fmt_jpeg_small' => [
						'size:50,90',
						'jpg',
					]
				]
			];

			/** @var AssetsManager|MockObject $managerMock */
			$managerMock = $this->getMockBuilder(AssetsManager::class)->getMock();


			$scanner = $this->mockAppSingleton(VirusScanner::class);
			$scanner
				->expects($this->never())
				->method('scanStream');

			$res1 = $this->resourceWithContent($content1);
			$res2 = $this->resourceWithContent($content2);

			$collection = new AssetsCollection($config, $managerMock);

			$managerMock
				->method('builder')
				->willReturnMap([
					[
						'jpg',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, '||jpg');

								rewind($res);

								$asset->setStorageOption('Meta1', 'value1');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path) {
								$path = \Safe\preg_replace('/\\..*?$/', '.jpg', $path);
							}
						),
					],
					[
						'size',
						$this->mockAssetBuilder(
							function (Asset $asset, $options) {
								$res = \Safe\fopen('php://temp', 'w+');

								\Safe\stream_copy_to_stream($asset->asResource(), $res);

								fwrite($res, "||{$options[0]}x{$options[1]}");

								rewind($res);

								$asset->setStorageOption('Meta2', 'value2');

								return new ResourceAsset($res, $asset->getMetaData(), $asset->getStorageOptions());
							},
							function (&$path, $options) {

								$path = \Safe\preg_replace('/\\.(.*?)$/', "_{$options[0]}x{$options[1]}.$1", $path);
							}
						),
					],
				]);


			$this->assertSame($collection, $collection->put('the/test/file.png', $res1, false));
			$this->assertSame($collection, $collection->put('the/test/file2.png', $res2, false));


			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file.png'));
			$this->assertSame(false, Storage::disk('testing_public')->exists('public_root/_/the/test/file2.png'));


			Storage::disk('testing_public')->put('public_root/_/the/test/file.png', 'existingContent');


			$this->assertSame($collection, $collection->publish('the/test/file.png', false));
			$this->assertSame($collection, $collection->publish('the/test/file2.png', false));


			$this->assertSame($content1, Storage::disk('testing')->get('my/path/the/test/file.png'));
			$this->assertSame('existingContent', Storage::disk('testing_public')->get('public_root/_/the/test/file.png'));
			$this->assertSame("{$content1}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file.jpg'));
			$this->assertSame("{$content1}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file_50x90.jpg'));

			$this->assertSame($content2, Storage::disk('testing')->get('my/path/the/test/file2.png'));
			$this->assertSame($content2, Storage::disk('testing_public')->get('public_root/_/the/test/file2.png'));
			$this->assertSame("{$content2}||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg/the/test/file2.jpg'));
			$this->assertSame("{$content2}||50x90||jpg", Storage::disk('testing_public')->get('public_root/fmt_jpeg_small/the/test/file2_50x90.jpg'));


		}


	}

	class TestingMemoryAdapter extends MemoryAdapter {
		/**
		 * @inheritDoc
		 */
		public function update($path, $contents, Config $config) {
			$ret =  parent::update($path, $contents, $config);

			$this->storage[$path]['passedConfig'] = $config;

			return $ret;
		}

		public function getPassedConfig(string $path) {
			return $this->storage[$path]['passedConfig'] ?? null;
		}


	}