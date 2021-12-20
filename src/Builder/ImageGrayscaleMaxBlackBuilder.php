<?php

	namespace MehrIt\LeviAssets\Builder;

	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviImages\Raster\Filter\GrayscaleMaxBlackFilter;

	class ImageGrayscaleMaxBlackBuilder extends AbstractImageAssetBuilder
	{

		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {
			
			// create filter
			/** @var GrayscaleMaxBlackFilter $filter */
			$filter = app(GrayscaleMaxBlackFilter::class);

			// apply filter
			$image = $filter->apply($asset->getImage());

			// return a new asset
			return new ImageAsset(
				$image,
				$asset->getMetaData(),
				$asset->getStorageOptions()
			);
		}
		
	}