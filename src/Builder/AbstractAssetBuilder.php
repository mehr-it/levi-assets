<?php


	namespace MehrIt\LeviAssets\Builder;


	use MehrIt\LeviAssets\Contracts\AssetBuilder;

	abstract class AbstractAssetBuilder implements AssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			// by default we simply pass-through
			return $resource;
		}

		/**
		 * @inheritDoc
		 */
		public function processPath(string &$path, array $options = []): AssetBuilder {

			// by default we keep the path unchanged
			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function cleanup(): AssetBuilder {

			// there is nothing to cleanup
			return $this;
		}


	}