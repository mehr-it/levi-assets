<?php /** @noinspection PhpComposerExtensionStubsInspection */


	namespace MehrIt\LeviAssets\Builder;


	use finfo;
	use MehrIt\LeviAssets\Contracts\Asset;
	use Safe\Exceptions\FilesystemException;

	class ContentTypeBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 * @throws FilesystemException
		 */
		public function build(Asset $asset, array $options = []): Asset {
			
			if (count($options)) {
				$asset->setMeta('Content-Type', implode('; ', $options));
			}
			else {
				$detected = $this->detectContentType($asset->asResource());
				if ($detected && $detected != 'application/octet-stream')
					$asset->setMeta('Content-Type', $detected);
			}
			
			return $asset;
		}


		/**
		 * Detects the content type of the given resource
		 * @param resource $resource The resource
		 * @return string|null The content type or null if not detected
		 * @throws FilesystemException
		 */
		protected function detectContentType($resource): ?string {

			if (!class_exists('\\finfo'))
				return null;

			$fInfo = new finfo(FILEINFO_MIME);

			$mimeType = $fInfo->buffer(\Safe\fread($resource, 100000)) ?: null;

			\Safe\rewind($resource);

			return $mimeType;
		}
	}