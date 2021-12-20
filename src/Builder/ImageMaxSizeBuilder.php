<?php

	namespace MehrIt\LeviAssets\Builder;

	use Imagine\Image\ImageInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;

	class ImageMaxSizeBuilder extends AbstractImageAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {
			$w      = $options[0] ?? null;
			$h      = $options[1] ?? null;
			$filter = ($options[2] ?? null) ?: ImageInterface::FILTER_UNDEFINED;

			// check for valid options
			if (is_numeric($w) && is_numeric($h) && trim($filter) !== '' && $w >= 1 && $h >= 1) {

				$image = $asset->getImage();

				$size = $image->getSize();
				if ($size->getWidth() > $w || $size->getHeight() > $h) {

					// scale image to fit into max size
					$image->resize(
						$size->scale(
							min(
								$w / $size->getWidth(),
								$h / $size->getHeight()
							)
						),
						$filter
					);
				}
			}

			return $asset;
		}


	}