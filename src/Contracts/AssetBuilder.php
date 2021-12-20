<?php


	namespace MehrIt\LeviAssets\Contracts;


	interface AssetBuilder
	{

		/**
		 * Builds the given asset
		 * @param Asset $asset The asset to build
		 * @param array $options The builder options
		 * @return Asset The builder output
		 */
		public function build(Asset $asset, array $options = []): Asset;

		/**
		 * Processes the given asset path
		 * @param string $path The path. This value can be modified by the builder to change the output path
		 * @param array $options The builder options
		 * @return AssetBuilder
		 */
		public function processPath(string &$path, array $options = []): AssetBuilder;

		/**
		 * Is called after each asset built to clean up any resources. The returned resource must not be closed by the builder - it is closed automatically by the collection.
		 * @return AssetBuilder
		 */
		public function cleanup(): AssetBuilder;

	}