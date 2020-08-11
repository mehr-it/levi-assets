<?php


	namespace MehrIt\LeviAssets\Builder;


	class ContentLanguageBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			if (count($options))
				$writeOptions['Content-Language'] = implode(', ', $options);

			return $resource;
		}
	}