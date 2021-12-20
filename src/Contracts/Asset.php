<?php

	namespace MehrIt\LeviAssets\Contracts;

	interface Asset
	{

		/**
		 * Returns the asset as resource. Calling this multiple times does not guarantee to return the same source.
		 * @return resource The resource
		 */
		public function asResource();

		/**
		 * Sets meta data
		 * @param string $key The key (case-insensitive)
		 * @param mixed $value The value
		 * @return Asset The asset
		 */
		public function setMeta(string $key, $value): Asset;

		/**
		 * Gets all meta data
		 * @return array The meta data
		 */
		public function getMetaData(): array;

		/**
		 * Gets meta data
		 * @param string $key The key (case-insensitive)
		 * @param mixed $default The default value
		 * @return mixed The meta data value or the default value if not set
		 */
		public function getMeta(string $key, $default = null);

		/**
		 * Sets the given storage option
		 * @param string $key The ky
		 * @param mixed $value The value
		 * @return Asset This instance
		 */
		public function setStorageOption(string $key, $value): Asset;

		/**
		 * Gets the given storage options
		 * @param string $key The key
		 * @param mixed $default The default value
		 * @return mixed The storage option or the default value if not set
		 */
		public function getStorageOption(string $key, $default = null);
		
		/**
		 * Gets the storage options
		 * @return array The storage options
		 */
		public function getStorageOptions(): array;
		
	}