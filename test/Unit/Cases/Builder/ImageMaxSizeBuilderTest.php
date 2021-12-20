<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use Imagine\Image\Box;
	use Imagine\Image\ImageInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\ImageMaxSizeBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class ImageMaxSizeBuilderTest extends TestCase
	{

		public function testBuild_widthExceeded() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(180, 40));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(90, 20), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_heightExceeded() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(80, 75));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(53, 50), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_bothExceededWidthGreatestDelta() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(180, 60));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(90, 30), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_bothExceededHeightGreatestDelta() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(100, 75));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(67, 50), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_sizeEqualsMaxSize() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(90, 50));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		
		public function testBuild_sizeIsSmaller() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(80, 40));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}

		public function testBuild_withFilter() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(180, 40));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(90, 20), ImageInterface::FILTER_CUBIC)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['90', '50', ImageInterface::FILTER_CUBIC];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_withoutOptions() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(180, 40));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = [];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}

		public function testBuild_fromResourceAsset() {


			$asset = new ResourceAsset($this->resourceWithContent($this->png10Pix()), [], []);

			$builder = new ImageMaxSizeBuilder();

			$options = ['5', '5'];

			/** @var ImageAsset $asset */
			$asset = $builder->build($asset, $options);

			$this->assertEquals(new Box(5,5), $asset->getImage()->getSize());
		}
	}