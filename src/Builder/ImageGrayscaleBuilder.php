<?php

	namespace MehrIt\LeviAssets\Builder;

	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviImages\Raster\Filter\GrayscaleAlphaFilter;

	class ImageGrayscaleBuilder extends AbstractImageAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {
			
			// create filter
			/** @var GrayscaleAlphaFilter $filter */
			$filter = app(GrayscaleAlphaFilter::class);

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