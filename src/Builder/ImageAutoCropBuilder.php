<?php

	namespace MehrIt\LeviAssets\Builder;

	use Imagine\Image\Palette\RGB;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviImages\Raster\Filter\AutoCropFilter;

	class ImageAutoCropBuilder extends AbstractImageAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {
			$background = strtolower($options[0] ?? null);
			$fuzz       = $options[1] ?? null;

			if (trim($background) === '')
				$background = 'ffffff';
			if (trim($fuzz) === '')
				$fuzz = '0.04';


			// check for valid options
			if (is_numeric($fuzz) && $fuzz >= 0 && $fuzz <= 1 && preg_match('/^[0-9a-f]{6}$/i', $background)) {

				// create filter
				/** @var AutoCropFilter $filter */
				$filter = app(AutoCropFilter::class, [
					'background'  => (new RGB())->color($background, 100),
					'sensitivity' => $fuzz,
				]);

				// apply filter
				$image = $filter->apply($asset->getImage());

				// return a new asset
				return new ImageAsset(
					$image,
					$asset->getMetaData(),
					$asset->getStorageOptions()
				);
			}

			return $asset;
		}


	}