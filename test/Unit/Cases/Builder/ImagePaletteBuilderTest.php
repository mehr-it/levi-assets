<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use Imagine\Image\Palette\CMYK;
	use Imagine\Image\Palette\Grayscale;
	use Imagine\Image\Palette\RGB;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Builder\ImagePaletteBuilder;
	use MehrIt\LeviImages\Facades\LeviImages;
	use MehrIt\LeviImages\Raster\Filter\PaletteFilter;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ImagePaletteBuilderTest extends TestCase
	{
		public function testBuild_usingMock_rgb() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			$filterMock = $this->getMockBuilder(PaletteFilter::class)->disableOriginalConstructor()->getMock();

			$filterMock
				->expects($this->once())
				->method('apply')
				->with($image)
				->willReturn($image);

			app()->bind(PaletteFilter::class, function ($app, $params) use ($filterMock) {
				
				$this->assertInstanceOf(RGB::class, $params['palette']);

				return $filterMock;
			});


			/** @var ImageAsset $retAsset */
			$retAsset = (new ImagePaletteBuilder())->build($asset, ['rgb']);

			$this->assertSame($image, $retAsset->getImage());
		}
		
		public function testBuild_usingMock_grayscale() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			$filterMock = $this->getMockBuilder(PaletteFilter::class)->disableOriginalConstructor()->getMock();

			$filterMock
				->expects($this->once())
				->method('apply')
				->with($image)
				->willReturn($image);

			app()->bind(PaletteFilter::class, function ($app, $params) use ($filterMock) {
				
				$this->assertInstanceOf(Grayscale::class, $params['palette']);

				return $filterMock;
			});


			/** @var ImageAsset $retAsset */
			$retAsset = (new ImagePaletteBuilder())->build($asset, ['grayscale']);

			$this->assertSame($image, $retAsset->getImage());
		}
		
		public function testBuild_usingMock_cmyk() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			$filterMock = $this->getMockBuilder(PaletteFilter::class)->disableOriginalConstructor()->getMock();

			$filterMock
				->expects($this->once())
				->method('apply')
				->with($image)
				->willReturn($image);

			app()->bind(PaletteFilter::class, function ($app, $params) use ($filterMock) {
				
				$this->assertInstanceOf(CMYK::class, $params['palette']);

				return $filterMock;
			});


			/** @var ImageAsset $retAsset */
			$retAsset = (new ImagePaletteBuilder())->build($asset, ['cmyk']);

			$this->assertSame($image, $retAsset->getImage());
		}
		

		public function testBuild_withoutMock() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			/** @var ImageAsset $retAsset */
			$retAsset = (new ImagePaletteBuilder())->build($asset, ['rgb']);

			$this->assertInstanceOf(ImageAsset::class, $retAsset);

		}
	}