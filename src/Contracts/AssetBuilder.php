<?php


	namespace MehrIt\LeviAssets\Contracts;


	interface AssetBuilder
	{

		/**
		 * Builds the given asset resource
		 * @param resource $resource The input resource
		 * @param array $writeOptions Options to be passed to the file system when writing the asset. Can be modified by the builder.
		 * @param array $options The builder options
		 * @return resource The output resource
		 */
		public function build($resource, &$writeOptions = [], array $options = []);

		/**
		 * Processes the given asset path
		 * @param string $path The path. This value can be modified by the builder to change the output path
		 * @param array $options The builder options
		 * @return AssetBuilder
		 */
		public function processPath(string &$path, array $options = []): AssetBuilder;


		/**
		 * Is called after each asset built to cleanup any resources. The returned resource must not be closed by the builder - it is closed automatically by the collection.
		 * @return AssetBuilder
		 */
		public function cleanup(): AssetBuilder;

	}