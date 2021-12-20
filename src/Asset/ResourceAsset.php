<?php

	namespace MehrIt\LeviAssets\Asset;

	use InvalidArgumentException;

	class ResourceAsset extends AbstractAsset
	{
		/**
		 * @var resource
		 */
		protected $resource;

		/**
		 * Creates a new instance
		 * @param resource $resource The resource
		 * @param array $metaData The meta data
		 * @param array $storageOptions The storage options
		 */
		public function __construct($resource, array $metaData, array $storageOptions) {
			parent::__construct($metaData, $storageOptions);
			
			if (!is_resource($resource))
				throw new InvalidArgumentException('First parameter ist not a resource, got ' . gettype($resource));
			
			$this->resource = $resource;
		}


		/**
		 * @inheritDoc
		 */
		public function asResource() {

			\Safe\rewind($this->resource);
			
			return $this->resource;
		}


	}