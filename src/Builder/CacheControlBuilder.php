<?php


	namespace MehrIt\LeviAssets\Builder;


	use MehrIt\LeviAssets\Contracts\Asset;

	class CacheControlBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {
			
			if (count($options))
				$asset->setMeta('Cache-Control', implode(', ', $options));
			
			return $asset;
		}

	}