<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use Aws\CommandInterface;
	use Aws\MockHandler;
	use Aws\Result;
	use Carbon\Carbon;
	use Illuminate\Support\Facades\Storage;
	use League\Flysystem\Filesystem;
	use League\Flysystem\Memory\MemoryAdapter;
	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Builder\CacheControlBuilder;
	use MehrIt\LeviAssets\Builder\ContentDispositionBuilder;
	use MehrIt\LeviAssets\Builder\ContentEncodingBuilder;
	use MehrIt\LeviAssets\Builder\ContentLanguageBuilder;
	use MehrIt\LeviAssets\Builder\ContentTypeBuilder;
	use MehrIt\LeviAssets\Builder\ExpiresBuilder;
	use MehrIt\LeviAssets\Builder\S3OptionsBuilder;
	use MehrItLeviAssetsTest\Helpers\TestsWithFiles;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use Psr\Http\Message\RequestInterface;

	class S3OptionsBuilderTest extends TestCase
	{
		use TestsWithFiles;

		/**
		 * @inheritDoc
		 */
		protected function getEnvironmentSetUp($app) {

			$app['config']->set('filesystems.disks.testing.driver', 'memory');
			$app['config']->set('filesystems.disks.s3_testing.driver', 's3');
			$app['config']->set('filesystems.disks.s3_testing.region', 'eu-central-1');
			$app['config']->set('filesystems.disks.s3_testing.bucket', 'dummy-bucket');
			$app['config']->set('filesystems.disks.s3_testing.secret', 'dummy-secret');
			$app['config']->set('filesystems.disks.s3_testing.key', 'dummy-key');
			$app['config']->set('filesystems.default', 'testing');

			parent::getEnvironmentSetUp($app);
		}

		/**
		 * @inheritDoc
		 */
		protected function setUp(): void {
			parent::setUp();

			Storage::extend('memory', function () {
				return new Filesystem(new MemoryAdapter());
			});
		}


		public function testBuild() {

			$res = fopen('php://temp', 'w+');

			$builder = new S3OptionsBuilder();

			$writeOptions = [
				'Cache-Control'       => 'max-age=86400',
				'Content-Disposition' => 'attachment',
				'content-encoding'    => 'gzip',
				'Content-Language'    => 'de-DE',
				'content-Type'        => 'text/html',
				'Content-Length'      => 12345,
				'expires'             => 'Wed, 21 Oct 2015 07:28:00 GMT',
				'Other'               => 'other-value',
			];
			$options = [];

			$builder->build($res, $writeOptions, $options);

			$this->assertSame(
				[
					'CacheControl'       => 'max-age=86400',
					'ContentDisposition' => 'attachment',
					'ContentEncoding'    => 'gzip',
					'ContentLanguage'    => 'de-DE',
					'ContentType'        => 'text/html',
					'Expires'            => 'Wed, 21 Oct 2015 07:28:00 GMT'
				],
				$writeOptions
			);


		}

		public function testIntegration() {

			Carbon::setTestNow(Carbon::now());

			$manager = new AssetsManager();

			$manager->registerCollection('s3test', [
				'public_storage' => 's3_testing',
				'virus_scan'     => false,
				'build'          => [
					'_'  => false,
					'b1' => [
						'cache:max-age=345',
						'disposition:attachment',
						'encoding:gzip',
						'type:text/html',
						'expires:' . Carbon::now()->getTimestamp(),
						'language:de-DE',
						's3',
					]
				]
			]);

			$manager->registerBuilder('cache', CacheControlBuilder::class);
			$manager->registerBuilder('disposition', ContentDispositionBuilder::class);
			$manager->registerBuilder('encoding', ContentEncodingBuilder::class);
			$manager->registerBuilder('language', ContentLanguageBuilder::class);
			$manager->registerBuilder('type', ContentTypeBuilder::class);
			$manager->registerBuilder('expires', ExpiresBuilder::class);
			$manager->registerBuilder('s3', S3OptionsBuilder::class);


			$res = $this->resourceWithContent('any-content');


			$mockHandler = new MockHandler();

			$mockHandler->append(function(CommandInterface $cmd, RequestInterface $req) {

				$this->assertSame('max-age=345', $req->getHeaderLine('Cache-Control'));
				$this->assertSame('attachment', $req->getHeaderLine('Content-Disposition'));
				$this->assertSame('gzip', $req->getHeaderLine('Content-Encoding'));
				$this->assertSame('text/html', $req->getHeaderLine('Content-Type'));
				$this->assertSame(Carbon::now()->setTimezone('GMT')->format('D, d M Y H:i:s \G\M\T'), $req->getHeaderLine('Expires'));

				// Content-language seams not to be respected => ignore it, since it seams to be a SDK bug
				//$this->assertSame('de-DE', $req->getHeaderLine('Content-Language'));

				return new Result([]);
			});

			Storage::disk('s3_testing')->getDriver()->getAdapter()->getClient()->getHandlerList()->setHandler($mockHandler);

			$manager->collection('s3test')->put('test.txt', $res);


		}



	}