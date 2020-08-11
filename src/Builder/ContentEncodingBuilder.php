<?php


	namespace MehrIt\LeviAssets\Builder;


	class ContentEncodingBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			if (count($options))
				$writeOptions['Content-Encoding'] = implode(', ', $options);

			return $resource;
		}
	}