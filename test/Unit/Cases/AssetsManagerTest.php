<?php


	namespace MehrItLeviAssetsTest\Unit\Cases;


	use Illuminate\Http\Request;
	use MehrIt\LeviAssets\AssetsManager;
	use MehrIt\LeviAssets\Contracts\AssetBuilder;
	use MehrIt\LeviAssets\Contracts\AssetsCollection;
	use MehrIt\LeviAssetsLinker\Contracts\AssetLinkFilter;
	use PHPUnit\Framework\MockObject\MockObject;
	use Psr\Http\Message\RequestInterface;

	class AssetsManagerTest extends TestCase
	{

		public function testRegisterBuilderAndRetrieve() {

			/** @var AssetBuilder|MockObject $builder1 */
			$builder1 = $this->getMockBuilder(AssetBuilder::class)->getMock();

			/** @var AssetBuilder|MockObject $builder2 */
			$builder2 = $this->getMockBuilder(AssetBuilder::class)->getMock();

			app()->bind('appClass1', function() use ($builder1) {
				return $builder1;
			});
			app()->bind('appClass2', function() use ($builder2) {
				return $builder2;
			});

			$manager = new AssetsManager();

			$manager->registerBuilder('myBuilder1', 'appClass1');
			$manager->registerBuilder('myBuilder2', 'appClass2');


			$this->assertSame($builder1, $manager->builder('myBuilder1'));
			$this->assertSame($builder2, $manager->builder('myBuilder2'));
			$this->assertSame(null, $manager->builder('myBuilder3'));

		}

		public function testRegisterCollectionAndRetrieve() {


			$manager = new AssetsManager();

			$manager->registerCollection('c1', [
				'storage_path' => 'storage/root',
			]);
			$manager->registerCollection('c2', [
				'storage_path' => 'other/root',
			]);


			$c1 = $manager->collection('c1');
			$c2 = $manager->collection('c2');

			$this->assertSame([
				'storage_path' => 'storage/root',
			], $c1->getConfig());
			$this->assertSame([
				'storage_path' => 'other/root',
			], $c2->getConfig());
			$this->assertSame(null, $manager->collection('c3'));


			// check for singletons
			$this->assertSame($c1, $manager->collection('c1'));
			$this->assertSame($c2, $manager->collection('c2'));

		}


		public function testResolve() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve('c1', 'my/path');

			$this->assertSame(
				[
					'_'  => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withClosureFilters() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					function($builtName, $path) {

						return $builtName === '_' || $path === 'public/file/a1.jpg';

					}
				]
			);

			$this->assertSame(
				[
					'_'  => 'public/file/1.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withClosureFilter() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				function ($builtName, $path) {

					return $builtName === '_' || $path === 'public/file/a1.jpg';

				}

			);

			$this->assertSame(
				[
					'_'  => 'public/file/1.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withPfxFilter() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'pfx:b'
				]
			);

			$this->assertSame(
				[
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
				],
				$ret
			);
		}

		public function testResolve_withPfxFilter_noArray() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				'pfx:b'
			);

			$this->assertSame(
				[
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
				],
				$ret
			);
		}

		public function testResolve_withPfxFilter_multipleArguments() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'pfx:a,b'
				]
			);

			$this->assertSame(
				[
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withSfxFilter() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'sfx:1'
				]
			);

			$this->assertSame(
				[
					'b1' => 'public/file/b1.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withSfxFilter_multipleArguments() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'sfx:1,2'
				]
			);

			$this->assertSame(
				[
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withListFilter() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'a1'
				]
			);

			$this->assertSame(
				[
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withListFilter_multipleArguments() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'a1,b2'
				]
			);

			$this->assertSame(
				[
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withMultipleFilters() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				[
					'pfx:a',
					'a1,b2'
				]
			);

			$this->assertSame(
				[
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testResolve_withMultipleFiltersAsString() {

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);

			$ret = $manager->resolve(
				'c1',
				'my/path',
				'pfx:a|a1,b1'
			);

			$this->assertSame(
				[
					'a1' => 'public/file/a1.jpg',
				],
				$ret
			);
		}

		public function testLink() {

			$request = Request::create('https://test.de/home.html');

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);


			$this->assertSame('https://test.de/public/file/1.jpg', $manager->link('c1', 'my/path', [], [], $request));
		}

		public function testLink_withPathFilters() {

			$request = Request::create('https://test.de/home.html');

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager();

			$manager->registerCollection('c1', []);


			$this->assertSame('https://test.de/public/file/a1.jpg', $manager->link('c1', 'my/path', ['pfx:a'], [], $request));
		}

		public function testLink_withPathAndLinkFilters() {

			$request = Request::create('https://test.de/home.html');

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager([
				'filters' => [
					'custom' => AssetsManagerTestLinkFilterB::class,
				]
			]);

			$manager->registerCollection('c1', []);


			$this->assertSame('https://test.de/public/file/b1.webp', $manager->link('c1', 'my/path', ['pfx:b'], ['custom:webp'], $request));
		}

		public function testLink_withPathAndCustomAndConfiguredLinkFilters() {

			$request = Request::create('https://test.de/home.html');

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->once())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_' => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			$collectionMock
				->method('linkFilters')
				->willReturn([
					['custom2', 'xls'],
				]);

			app()->bind(AssetsCollection::class, function() use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager([
				'filters' => [
					'custom'  => AssetsManagerTestLinkFilterB::class,
					'custom2' => AssetsManagerTestLinkFilterA::class,
				]
			]);

			$manager->registerCollection('c1', []);



			$this->assertSame('https://test.de/public/file/b1.xls', $manager->link('c1', 'my/path', ['pfx:b'], ['custom'], $request));
		}


		public function testLink_requestFromKernel() {

			$request = Request::create('https://test.de/home.html');

			$kernel = app(\Illuminate\Contracts\Http\Kernel::class);

			$kernel->handle($request);

			/** @var MockObject|AssetsCollection $collectionMock */
			$collectionMock = $this->getMockBuilder(AssetsCollection::class)->getMock();
			$collectionMock
				->expects($this->atLeastOnce())
				->method('resolve')
				->with('my/path')
				->willReturn([
					'_'  => 'public/file/1.jpg',
					'b1' => 'public/file/b1.jpg',
					'b2' => 'public/file/b2.jpg',
					'a1' => 'public/file/a1.jpg',
				]);

			app()->bind(AssetsCollection::class, function () use ($collectionMock) {
				return $collectionMock;
			});

			$manager = new AssetsManager([
				'filters' => [
					'log' => AssetsManagerTestLinkFilterLogging::class,
				]
			]);

			$manager->registerCollection('c1', []);


			$this->assertSame('https://test.de/public/file/1.jpg', $manager->link('c1', 'my/path', [], 'log'));

			$loggedRequest = AssetsManagerTestLinkFilterLogging::$loggedRequest;
			$this->assertInstanceOf(RequestInterface::class, $loggedRequest);
			$this->assertSame('https://test.de/home.html', (string)$loggedRequest->getUri());

			// execute again and check if request is still in cache and not re-converted to PSR every time
			$this->assertSame('https://test.de/public/file/1.jpg', $manager->link('c1', 'my/path', [], 'log'));
			$this->assertSame($loggedRequest, AssetsManagerTestLinkFilterLogging::$loggedRequest);
		}
	}

	class AssetsManagerTestLinkFilterB implements AssetLinkFilter {
		/**
		 * @inheritDoc
		 */
		public function filterPaths(RequestInterface $request, array &$paths, array $options = []): AssetLinkFilter {

			$ret = [];

			foreach($paths as $key => $value) {
				if ($key[0] === 'b')
					$ret[$key] = $value;
			}

			$paths = $ret;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function processLink(RequestInterface $request, string &$link, array $options = []): AssetLinkFilter {
			if ($options[0] ?? null)
				$link = \Safe\preg_replace('/\\.[^.]*?$/', '.' . $options[0], $link);

			return $this;
		}


	}

	class AssetsManagerTestLinkFilterA implements AssetLinkFilter {
		/**
		 * @inheritDoc
		 */
		public function filterPaths(RequestInterface $request, array &$paths, array $options = []): AssetLinkFilter {


			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function processLink(RequestInterface $request, string &$link, array $options = []): AssetLinkFilter {
			if ($options[0] ?? null)
				$link = \Safe\preg_replace('/\\.[^.]*?$/', '.' . $options[0], $link);

			return $this;
		}


	}

	class AssetsManagerTestLinkFilterLogging implements AssetLinkFilter {

		public static $loggedRequest = null;

		/**
		 * @inheritDoc
		 */
		public function filterPaths(RequestInterface $request, array &$paths, array $options = []): AssetLinkFilter {

			static::$loggedRequest = $request;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function processLink(RequestInterface $request, string &$link, array $options = []): AssetLinkFilter {
			return $this;
		}


	}