<?php

	namespace MehrIt\LeviAssets\Builder;

	use Imagine\Image\Palette\RGB;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviImages\Facades\LeviImages;
	use MehrIt\LeviImages\Raster\Filter\CanvasFilter;

	class ImageCanvasBuilder extends AbstractImageAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {
			$background = strtolower($options[0] ?? null);
			$margin     = $options[1] ?? null;

			if (trim($background) === '')
				$background = 'ffffff';
			if (trim($margin) === '')
				$margin = '0';


			// check for valid options
			if (is_numeric($margin) && $margin >= 0 && preg_match('/^[0-9a-f]{6}$/i', $background)) {

				// create filter
				/** @var CanvasFilter $filter */
				$filter = app(CanvasFilter::class, [
					'imagine'    => LeviImages::raster()->imagine(),
					'background' => (new RGB())->color($background, 100),
					'margin'     => $margin,
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