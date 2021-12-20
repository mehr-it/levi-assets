<?php


	namespace MehrIt\LeviAssets\Builder;


	use MehrIt\LeviAssets\Contracts\Asset;

	class ContentEncodingBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {
			
			if (count($options))
				$asset->setMeta('Content-Encoding', implode(', ', $options));
			
			return $asset;
		}
	}