<?php


	namespace MehrIt\LeviAssets\Builder;


	use Illuminate\Support\Arr;
	use Illuminate\Support\Str;
	use MehrIt\LeviAssets\Contracts\Asset;

	class S3OptionsBuilder extends AbstractAssetBuilder
	{
		const META_MAP = [
			'cache-control'       => 'CacheControl',
			'content-disposition' => 'ContentDisposition',
			'content-encoding'    => 'ContentEncoding',
			'content-language'    => 'ContentLanguage',
			'content-type'        => 'ContentType',
			'expires'             => 'Expires',
		];

		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {

			// here we convert some meta data to storage options
			foreach (self::META_MAP as $metaKey => $storageOptionKey) {
				$currValue = $asset->getMeta($metaKey);
				if ($currValue !== null)
					$asset->setStorageOption($storageOptionKey, $currValue);
			}

			return $asset;
		}

	}