<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Builder\ImageGrayscaleMaxBlackBuilder;
	use MehrIt\LeviImages\Facades\LeviImages;
	use MehrIt\LeviImages\Raster\Filter\GrayscaleMaxBlackFilter;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ImageGrayscaleMaxBlackBuilderTest extends TestCase
	{

		public function testBuild_usingMock() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			$filterMock = $this->getMockBuilder(GrayscaleMaxBlackFilter::class)->getMock();

			$filterMock
				->expects($this->once())
				->method('apply')
				->with($image)
				->willReturn($image);

			app()->bind(GrayscaleMaxBlackFilter::class, function () use ($filterMock) {
				return $filterMock;
			});


			/** @var ImageAsset $retAsset */
			$retAsset = (new ImageGrayscaleMaxBlackBuilder())->build($asset);

			$this->assertSame($image, $retAsset->getImage());

		}

		public function testBuild_withoutMock() {

			$image = LeviImages::raster()->load($this->png10Pix());

			$asset = new ImageAsset($image, [], []);

			/** @var ImageAsset $retAsset */
			$retAsset = (new ImageGrayscaleMaxBlackBuilder())->build($asset);

			$this->assertInstanceOf(ImageAsset::class, $retAsset);

		}

	}