<?php


	namespace MehrItLeviAssetsTest\Helpers;


	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use PHPUnit\Framework\MockObject\MockObject;
	use PHPUnit\Framework\SkippedTestError;

	trait TestsVirusScan
	{
		protected static $MALICIOUS_CONTENT_SEQUENCE_REV = '*H+H$!ELIF-TSET-SURIVITNA-DRADNATS-RACIE$}7)CC7)^P(45XZP\4[PA@%P!O5X';

		/**
		 * Gets a file content being detected as malicious content
		 * @return string
		 */
		protected function getMaliciousContentSequence() {
			return strrev(self::$MALICIOUS_CONTENT_SEQUENCE_REV);
		}

		/**
		 * Creates a new virus scanner instance
		 * @param int $timeout The timeout
		 * @param bool $bypass True if to bypass
		 * @param string|null $socket The socket if to overwrite setting
		 * @return VirusScanner
		 */
		protected function createVirusScanner(int $timeout = 30, bool $bypass = false, string $socket = null) {
			if (!$socket) {
				$socket = env('CLAMAV_SOCKET');
				if (!$socket)
					throw new SkippedTestError('Environment variable CLAMAV_SOCKET must be set for this test, eg. to "unix:///var/run/clamav/clamd.ctl"');
			}

			return new VirusScanner($socket, $timeout, $bypass);
		}

	}