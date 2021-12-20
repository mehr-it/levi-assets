<?php


	namespace MehrIt\LeviAssets\Builder;


	use DateTimeInterface;
	use MehrIt\LeviAssets\Contracts\Asset;

	class ExpiresBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build(Asset $asset, array $options = []): Asset {
			
			$date = $options[0] ?? null;
			if ($date !== null) {

				// convert timestamps and date objects to string
				if (is_int($date) || is_numeric($date))
					$date = gmdate('D, d M Y H:i:s \G\M\T', $date);
				elseif ($date instanceof DateTimeInterface)
					$date = gmdate('D, d M Y H:i:s \G\M\T', $date->getTimestamp());


				$asset->setMeta('Expires', $date);
			}
			
			return $asset;
		}
	}