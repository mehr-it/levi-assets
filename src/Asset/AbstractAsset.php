<?php

	namespace MehrIt\LeviAssets\Asset;

	use InvalidArgumentException;
	use MehrIt\LeviAssets\Contracts\Asset;

	abstract class AbstractAsset implements Asset
	{

		/**
		 * @var array
		 */
		protected $metaData = [];

		/**
		 * @var array
		 */
		protected $storageOptions = [];
		


		/**
		 * Creates a new instance
		 * @param array $metaData The meta data
		 * @param array $storageOptions The storage options
		 */
		protected function __construct(array $metaData, array $storageOptions) {
			$this->metaData       = array_change_key_case($metaData, CASE_LOWER);
			$this->storageOptions = $storageOptions;
		}


		/**
		 * @inheritDoc
		 */
		public function setMeta(string $key, $value): Asset {
			$this->metaData[strtolower($key)] = $value;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getMeta(string $key, $default = null) {

			$key = strtolower($key);

			if (!array_key_exists($key, $this->metaData))
				return $default;

			return $this->metaData[$key];
		}

		/**
		 * @inheritDoc
		 */
		public function getMetaData(): array {
			return $this->metaData;
		}


		/**
		 * @inheritDoc
		 */
		public function setStorageOption(string $key, $value): Asset {
			$this->storageOptions[$key] = $value;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function getStorageOption(string $key, $default = null) {
			if (!array_key_exists($key, $this->storageOptions))
				return $default;

			return $this->storageOptions[$key];
		}

		/**
		 * @inheritDoc
		 */
		public function getStorageOptions(): array {
			return $this->storageOptions;
		}


	}