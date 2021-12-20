<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use Imagine\Image\Box;
	use Imagine\Image\ImageInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\ImageMinSizeBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class ImageMinSizeBuilderTest extends TestCase
	{

		public function testBuild_widthUndercut() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(45, 60));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(90, 120), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_heightUndercut() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(100, 25));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(200, 50), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		
		public function testBuild_bothUndercutWidthGreatestDelta() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(45, 40));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(90, 80), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_bothUndercutHeightGreatestDelta() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(80, 10));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(400, 50), ImageInterface::FILTER_UNDEFINED)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_sizeEqual() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(90, 50));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_sizeGreater() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(100, 60));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_bypassScaleFactorExceeded() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(2, 2));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50'];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}
		
		public function testBuild_withoutOptions() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(2, 2));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = [];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}

		public function testBuild_withFilter() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(45, 60));
			$image
				->expects($this->once())
				->method('resize')
				->with(new Box(90, 120), ImageInterface::FILTER_CUBIC)
				->willReturnSelf();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50', ImageInterface::FILTER_CUBIC];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}

		public function testBuild_withBypassScaleFactor() {
			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();
			$image
				->method('getSize')
				->willReturn(new Box(80, 40));
			$image
				->expects($this->never())
				->method('resize');

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['90', '50', ImageInterface::FILTER_UNDEFINED, 1];

			$asset = $builder->build($asset, $options);

			$this->assertSame($image, $asset->getImage());
		}

		public function testBuild_fromResourceAsset() {


			$asset = new ResourceAsset($this->resourceWithContent($this->png1Pix()), [], []);

			$builder = new ImageMinSizeBuilder();

			$options = ['5', '5'];

			/** @var ImageAsset $asset */
			$asset = $builder->build($asset, $options);

			$this->assertEquals(new Box(5, 5), $asset->getImage()->getSize());
		}
	}