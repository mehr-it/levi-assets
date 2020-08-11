<?php


	namespace MehrIt\LeviAssets\Builder;


	class CacheControlBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			if (count($options))
				$writeOptions['Cache-Control'] = implode(', ', $options);

			return $resource;
		}


	}