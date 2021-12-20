<?php


	namespace MehrIt\LeviAssets\Builder;


	use MehrIt\LeviAssets\Contracts\Asset;

	class ContentLanguageBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {
			
			if (count($options))
				$asset->setMeta('Content-Language', implode(', ', $options));
			
			return $asset;
		}
	}