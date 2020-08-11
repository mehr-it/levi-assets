<?php


	namespace MehrIt\LeviAssets\Builder;


	class ContentDispositionBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			if (count($options))
				$writeOptions['Content-Disposition'] = implode('; ', $options);

			return $resource;
		}
	}