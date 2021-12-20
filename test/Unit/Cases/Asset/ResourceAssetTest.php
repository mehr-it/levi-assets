<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Asset;

	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ResourceAssetTest extends TestCase
	{

		public function testConstructGetters() {
			
			$res = fopen('php://memory', 'w+');
			
			$asset = new ResourceAsset(
				$res,
				[
					'Meta1' => 'value1',
					'Meta2' => 'value2',
				],
				[
					'Storage1' => 's1',
					'Storage2' => 's2',
				]
			);
			
			$this->assertSame(
				[
					'meta1' => 'value1',
					'meta2' => 'value2',
				],
				$asset->getMetaData()
			);
			
			$this->assertSame(
				[
					'Storage1' => 's1',
					'Storage2' => 's2',
				],
				$asset->getStorageOptions()
			);
			
			$this->assertSame($res, $asset->asResource());
		}
		
		public function testGetSetMeta() {

			$res = fopen('php://memory', 'w+');

			$asset = new ResourceAsset(
				$res,
				[
					'Meta1' => 'value1',
					'Meta2' => 'value2',
				],
				[]
			);

			$default = new \stdClass();
			
			$this->assertSame($asset, $asset->setMeta('Meta1', 'newValue1'));
			$this->assertSame($asset, $asset->setMeta('meta3', 'newValue3'));

			$this->assertSame('newValue1', $asset->getMeta('meta1'));
			$this->assertSame('value2', $asset->getMeta('Meta2'));
			$this->assertSame('newValue3', $asset->getMeta('Meta3', $default));
			$this->assertSame('newValue3', $asset->getMeta('meta3', $default));
			
			$this->assertSame($default, $asset->getMeta('Meta4', $default));

			$this->assertSame(
				[
					'meta1' => 'newValue1',
					'meta2' => 'value2',
					'meta3' => 'newValue3',
				],
				$asset->getMetaData()
			);
		}
		
		public function testGetSetStorageOption() {

			$res = fopen('php://memory', 'w+');

			$asset = new ResourceAsset(
				$res,
				[],
				[
					'Storage1' => 'value1',
					'Storage2' => 'value2',
				]
			);
			
			$default = new \stdClass();
			
			$this->assertSame($asset, $asset->setStorageOption('Storage1', 'newValue1'));
			$this->assertSame($asset, $asset->setStorageOption('storage3', 'newValue3'));

			$this->assertSame('newValue1', $asset->getStorageOption('Storage1'));
			$this->assertSame('value2', $asset->getStorageOption('Storage2'));
			$this->assertSame('newValue3', $asset->getStorageOption('storage3', $default));
			
			
			$this->assertSame($default, $asset->getStorageOption('Storage3', $default));

			$this->assertSame(
				[
					'Storage1' => 'newValue1',
					'Storage2' => 'value2',
					'storage3' => 'newValue3',
				],
				$asset->getStorageOptions()
			);
		}

		public function testAsResource() {

			$res = fopen('php://memory', 'w+');
			
			\Safe\fwrite($res, 'a test');

			$asset = new ResourceAsset($res, [], []);

			$this->assertSame('a test', stream_get_contents($asset->asResource()));
			
			// stream should be rewinded before it is returned
			$this->assertSame('a test', stream_get_contents($asset->asResource()));
			
			
		}
		
		
		
	}