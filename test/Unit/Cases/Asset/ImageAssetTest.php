<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Asset;

	use Imagine\Image\ImageInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class ImageAssetTest extends TestCase
	{
		public function testConstructGetters() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->expects($this->once())
				->method('get')
				->with('png', [])
				->willReturn('png-file-content');
			
			$asset = new ImageAsset(
				$image,
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

			$this->assertSame('png-file-content', stream_get_contents($asset->asResource()));
			
			$this->assertSame($image, $asset->getImage());
		}

		public function testGetSetMeta() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset(
				$image,
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

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();


			$asset = new ImageAsset(
				$image,
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

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->expects($this->exactly(2))
				->method('get')
				->with('png', [])
				->willReturn('png-file-content');
			

			$asset = new ImageAsset($image, [], []);

			$this->assertSame('png-file-content', stream_get_contents($asset->asResource()));

			// stream should be new before it is returned
			$this->assertSame('png-file-content', stream_get_contents($asset->asResource()));


		}
		
		public function testAsResource_withFormatAndOptions() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->expects($this->exactly(2))
				->method('get')
				->with('jpg', ['quality' => 95])
				->willReturn('jpg-file-content');
			

			$asset = new ImageAsset($image, [], []);
			
			$asset->setFormat('jpg');
			$asset->setFormatOptions(['quality' => 95]);
			

			$this->assertSame('jpg-file-content', stream_get_contents($asset->asResource()));

			// stream should be new before it is returned
			$this->assertSame('jpg-file-content', stream_get_contents($asset->asResource()));

		}
		
		public function testGetSetFormat() {
			
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);

			$this->assertSame($asset, $asset->setFormat('jpg'));
			
			$this->assertSame('jpg', $asset->getFormat());
		}
		
		public function testGetSetFormatOptions() {
			
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);
			
			$this->assertSame($asset, $asset->setFormatOptions(['quality' => 95]));
			
			$this->assertSame(['quality' => 95], $asset->getFormatOptions());
		}
	}