<?php

	namespace MehrIt\LeviAssets\Builder;

	use Imagine\Image\ImageInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;

	class ImageMinSizeBuilder extends AbstractImageAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {

			$w                      = $options[0] ?? null;
			$h                      = $options[1] ?? null;
			$filter                 = ($options[2] ?? null) ?: ImageInterface::FILTER_UNDEFINED;
			$bypassUpperScaleFactor = ($options[3] ?? null) ?: 10;

			// check for valid options
			if (is_numeric($w) && is_numeric($h) && trim($filter) !== '' && is_numeric($bypassUpperScaleFactor) && $w >= 1 && $h >= 1) {

				$image = $asset->getImage();

				$size = $image->getSize();
				if ($size->getWidth() < $w || $size->getHeight() < $h) {

					// calculate scale factor (the greatest of both edges)
					$scaleFactor = max(
						$w / $size->getWidth(),
						$h / $size->getHeight()
					); 
					
					// only do upscale, if not reached the bypass scale factor
					if ($scaleFactor < $bypassUpperScaleFactor) {

						// scale image to fit to cover min size
						$image->resize(
							$size->scale($scaleFactor),
							$filter
						);
					}
				}
			}

			return $asset;


		}


	}