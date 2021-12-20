<?php


	namespace MehrIt\LeviAssets\Builder;


	use MehrIt\LeviAssets\Contracts\Asset;

	class ContentDispositionBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {
			
			if (count($options))
				$asset->setMeta('Content-Disposition', implode('; ', $options));
			
			return $asset;
		}
	}