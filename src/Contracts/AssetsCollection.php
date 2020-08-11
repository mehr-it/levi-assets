<?php


	namespace MehrIt\LeviAssets\Contracts;


	use Generator;

	interface AssetsCollection
	{
		/**
		 * Gets the link filters to apply to the current collection
		 * @return array The filter definitions. Each definition is an array with the filter name followed by any arguments
		 */
		public function linkFilters(): array;


		/**
		 * Resolves the asset with the given path
		 * @param string $path The asset path
		 * @return string[] The asset public paths. Built name as prefix
		 */
		public function resolve(string $path): array;

		/**
		 * Puts the given asset to the collection
		 * @param string $path The target path
		 * @param resource $resource The resource stream
		 * @param bool $publish True if to automatically publish the asset
		 * @return AssetsCollection This instance
		 */
		public function put(string $path, $resource, bool $publish = true): AssetsCollection;

		/**
		 * Deletes the given asset from the collection
		 * @param string $path The asset path
		 * @return AssetsCollection This instance
		 */
		public function delete(string $path): AssetsCollection;

		/**
		 * Gets the given asset from the collection
		 * @param string $path The path
		 * @return null|resource The asset data
		 */
		public function get(string $path);

		/**
		 * Checks if the given asset exists
		 * @param string $path The path
		 * @return bool True if existing. Else false.
		 */
		public function exists(string $path): bool;

		/**
		 * Returns an iterator to iterate over all assets
		 * @return Generator|string[] The iterable. The path names.
		 */
		public function iterate(): iterable;

		/**
		 * Returns an iterator to iterate over all files in the public directory
		 * @return Generator|string[] The iterable. The path names.
		 */
		public function iteratePublic(): iterable;

		/**
		 * Deletes the given public file
		 * @param string $publicPath The public file path
		 * @return AssetsCollection This instance
		 */
		public function deletePublic(string $publicPath): AssetsCollection;

		/**
		 * Compiles the given asset
		 * @param string $path The asset path
		 * @param bool $force True if to recompile existing compilations
		 * @return AssetsCollection This instance
		 */
		public function publish(string $path, bool $force = true): AssetsCollection;

		/**
		 * Withdraws (deletes) all built files for the given asset from the public storage
		 * @param string $path The asset path
		 * @return AssetsCollection This instance
		 */
		public function withdraw(string $path): AssetsCollection;

	}