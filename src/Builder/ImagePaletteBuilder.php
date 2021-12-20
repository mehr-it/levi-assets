<?php

	namespace MehrIt\LeviAssets\Builder;

	use Imagine\Image\Palette\CMYK;
	use Imagine\Image\Palette\Grayscale;
	use Imagine\Image\Palette\RGB;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviImages\Raster\Filter\PaletteFilter;

	class ImagePaletteBuilder extends AbstractImageAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {
			$palette = strtolower($options[0] ?? null);


			switch (trim($palette)) {
				case 'rgb':
					$pal = new RGB();
					break;
				case 'grayscale':
					$pal = new Grayscale();
					break;
				case 'cmyk':
					$pal = new CMYK();
					break;
				default:
					return $asset;

			}

			// create filter
			/** @var PaletteFilter $filter */
			$filter = app(PaletteFilter::class, [
				'palette' => $pal,
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
	}