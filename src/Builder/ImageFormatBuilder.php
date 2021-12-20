<?php

	namespace MehrIt\LeviAssets\Builder;

	use Illuminate\Support\Arr;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Contracts\Asset;
	use MehrIt\LeviAssets\Contracts\AssetBuilder;

	class ImageFormatBuilder extends AbstractImageAssetBuilder
	{
		const EXTENSION_OPT = 'ext';

		/**
		 * @inheritDoc
		 */
		protected function buildImageAsset(ImageAsset $asset, array $options): Asset {

			$format = $this->format($options);
			if ($format) {
				$asset->setFormat($format);

				$asset->setFormatOptions(Arr::except(
					$this->extractFormatOptions($options),
					[self::EXTENSION_OPT]
				));
			}

			return $asset;
		}

		/**
		 * @inheritDoc
		 */
		public function processPath(string &$path, array $options = []): AssetBuilder {

			$ext = $this->extension($options);

			if ($ext !== null) {

				// remove existing extension
				$path = \Safe\preg_replace('_([^/])(\.[^./]*)$_', '$1', $path);
				
				if ($ext !== false)
					$path = "{$path}.{$ext}";
			}

			return $this;
		}


		/**
		 * Gets the format from the options
		 * @param array $options The options
		 * @return string The format
		 */
		protected function format(array $options): string {
			return strtolower($options[0] ?? '');
		}

		/**
		 * Returns the file extension to set for the asset
		 * @param array $options The build options
		 * @return mixed|string|null The file extension. Null if to leave untouched.
		 */
		protected function extension(array $options) {

			$formatOptions = $this->extractFormatOptions($options);
			$ext           = ($formatOptions[self::EXTENSION_OPT] ?? null);

			// fallback to default
			if ($ext === null)
				$ext = $this->defaultExtension($this->format($options));

			return $ext;
		}

		/**
		 * Gets the default file extension for the given format
		 * @param string $format The format
		 * @return string|null The file extension or null if not determinable
		 */
		protected function defaultExtension(string $format): ?string {

			if ($format === '')
				return null;

			return $format;
		}

		/**
		 * Extracts the format options from the options
		 * @param array $options The options
		 * @return array The format options
		 */
		protected function extractFormatOptions(array $options): array {

			// first option is the format
			array_shift($options);

			return $this->extractNamedOptions($options);
		}
	}