<?php

	namespace MehrIt\LeviAssets\Builder;
	
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviImages\Facades\LeviImages;

	abstract class AbstractImageAssetBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public final function build(Asset $asset, array $options = []): Asset {
			
			// if we don't have an image asset yet, we load the asset resource as image here
			if (!($asset instanceof ImageAsset)) {
				$image = LeviImages::raster()->read($asset->asResource());
				
				$asset = new ImageAsset(
					$image,
					$asset->getMetaData(), 
					$asset->getStorageOptions()
				);
			}
			
			return $this->buildImageAsset($asset, $options);
		}

		/**
		 * Builds the given image asset
		 * @param ImageAsset $asset The asset
		 * @param array $options The options passed to the builder
		 * @return Asset The built asset
		 */
		protected abstract function buildImageAsset(ImageAsset $asset, array $options): Asset;


	}