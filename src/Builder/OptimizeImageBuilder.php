<?php

	namespace MehrIt\LeviAssets\Builder;

	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use \LeviImages;
	use MehrIt\LeviImages\Optimization\Optimizer;

	class OptimizeImageBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {

			/** @var Optimizer $optimizer */
			$optimizer = LeviImages::optimizer();

			return new ResourceAsset(
				$optimizer->optimizeResource($asset->asResource()),
				$asset->getMetaData(),
				$asset->getStorageOptions()
			);
		}
		
	}