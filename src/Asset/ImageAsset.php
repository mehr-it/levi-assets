<?php

	namespace MehrIt\LeviAssets\Asset;

	use Imagine\Image\ImageInterface;

	class ImageAsset extends AbstractAsset
	{
			
		/**
		 * @var ImageInterface
		 */
		protected $image;

		/**
		 * @var string|null
		 */
		protected $format = 'png';
		
		protected $formatOptions = [];

		/**
		 * Creates a new instance
		 * @param ImageInterface $image The image
		 * @param array $metaData The meta data 
		 * @param array $storageOptions The storage options
		 */
		public function __construct(ImageInterface $image, array $metaData, array $storageOptions) {
			parent::__construct($metaData, $storageOptions);
			
			$this->image = $image;
		}

		/**
		 * Gets the format options
		 * @return array The format options
		 */
		public function getFormatOptions(): array {
			return $this->formatOptions;
		}

		/**
		 * Sets the format options
		 * @param array $formatOptions The format options
		 * @return ImageAsset
		 */
		public function setFormatOptions(array $formatOptions): ImageAsset {
			$this->formatOptions = $formatOptions;

			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function asResource() {

			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $this->image->get($this->format, $this->formatOptions));

			\Safe\rewind($resource);
			
			return $resource;
		}
		
		public function getImage(): ImageInterface {
						
			return $this->image;
		}

		/**
		 * Gets the output format
		 * @return string The format
		 */
		public function getFormat(): string {
			return $this->format;
		}

		/**
		 * Sets the output format
		 * @param string $format The format
		 * @return ImageAsset
		 */
		public function setFormat(string $format): ImageAsset {
			$this->format = $format;

			return $this;
		}
		
		


	}