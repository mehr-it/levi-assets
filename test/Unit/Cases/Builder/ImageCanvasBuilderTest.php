<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use Imagine\Image\Palette\Color\ColorInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Builder\ImageCanvasBuilder;
	use MehrIt\LeviImages\Facades\LeviImages;
	use MehrIt\LeviImages\Raster\Filter\CanvasFilter;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ImageCanvasBuilderTest extends TestCase
	{
		public function testBuild_usingMock() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			$filterMock = $this->getMockBuilder(CanvasFilter::class)->disableOriginalConstructor()->getMock();

			$filterMock
				->expects($this->once())
				->method('apply')
				->with($image)
				->willReturn($image);

			app()->bind(CanvasFilter::class, function ($app, $params) use ($filterMock) {

				$background  = $params['background'];
				$margin = $params['margin'];

				$this->assertEquals(255, $background->getValue(ColorInterface::COLOR_RED));
				$this->assertEquals(255, $background->getValue(ColorInterface::COLOR_GREEN));
				$this->assertEquals(255, $background->getValue(ColorInterface::COLOR_BLUE));
				$this->assertEquals(100, $background->getAlpha());

				$this->assertEquals(0, $margin);

				return $filterMock;
			});


			/** @var ImageAsset $retAsset */
			$retAsset = (new ImageCanvasBuilder())->build($asset);

			$this->assertSame($image, $retAsset->getImage());

		}

		public function testBuild_usingMockWithOptions() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			$filterMock = $this->getMockBuilder(CanvasFilter::class)->disableOriginalConstructor()->getMock();

			$filterMock
				->expects($this->once())
				->method('apply')
				->with($image)
				->willReturn($image);

			app()->bind(CanvasFilter::class, function ($app, $params) use ($filterMock) {

				$background  = $params['background'];
				$margin      = $params['margin'];

				$this->assertEquals(255, $background->getValue(ColorInterface::COLOR_RED));
				$this->assertEquals(0, $background->getValue(ColorInterface::COLOR_GREEN));
				$this->assertEquals(0, $background->getValue(ColorInterface::COLOR_BLUE));
				$this->assertEquals(100, $background->getAlpha());

				$this->assertEquals(5, $margin);

				return $filterMock;
			});


			/** @var ImageAsset $retAsset */
			$retAsset = (new ImageCanvasBuilder())->build($asset, ['ff0000', '5']);

			$this->assertSame($image, $retAsset->getImage());

		}

		public function testBuild_withoutMock() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			/** @var ImageAsset $retAsset */
			$retAsset = (new ImageCanvasBuilder())->build($asset);

			$this->assertInstanceOf(ImageAsset::class, $retAsset);

		}
	}