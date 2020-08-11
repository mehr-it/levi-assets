<?php /** @noinspection PhpComposerExtensionStubsInspection */


	namespace MehrIt\LeviAssets\Builder;


	use finfo;

	class ContentTypeBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			if (count($options)) {
				$writeOptions['Content-Type'] = implode('; ', $options);
			}
			else {
				$detected = $this->detectContentType($resource);
				if ($detected && $detected != 'application/octet-stream')
					$writeOptions['Content-Type'] = $detected;
			}

			return $resource;
		}


		/**
		 * Detects the content type of the given resource
		 * @param resource $resource The resource
		 * @return string|null The content type or null if not detected
		 * @throws \Safe\Exceptions\FilesystemException
		 */
		protected function detectContentType($resource): ?string {

			if (!class_exists('\\finfo'))
				return null;

			$finfo = new finfo(FILEINFO_MIME);

			$mimeType = $finfo->buffer(\Safe\fread($resource, 100000)) ?: null;

			\Safe\rewind($resource);

			return $mimeType;
		}
	}