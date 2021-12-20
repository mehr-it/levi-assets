<?php


	namespace MehrIt\LeviAssets\Builder;


	use MehrIt\LeviAssets\Contracts\AssetBuilder;

	abstract class AbstractAssetBuilder implements AssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function cleanup(): AssetBuilder {

			// there is nothing to clean up
			return $this;
		}

		/**
		 * @inheritDoc
		 */
		public function processPath(string &$path, array $options = []): AssetBuilder {
			
			// default implementation leaves the path unchanged
			
			return $this;
		}


		/**
		 * Extracts the named options from the options array
		 * @param array $options The options
		 * @return array The named options
		 */
		protected function extractNamedOptions(array $options): array {
			
			$ret = [];
			foreach ($options as $key => $currOption) {
				if (is_int($key)) {
					$sp = explode('=', (string)$currOption, 2);

					if (count($sp) == 2) {
						$key        = trim($sp[0]);
						$currOption = trim($sp[1]);
					}
					else {
						continue;
					}

				}

				$ret[$key] = $currOption;
			}

			return $ret;
		}

	}