<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Util\VirusScan;


	use MehrIt\LeviAssets\Util\VirusScan\VirusDetectedException;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanFailedException;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use MehrItLeviAssetsTest\Helpers\TestsWithFiles;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\SkippedTestError;

	class VirusScannerTest extends TestCase
	{
		use TestsWithFiles;

		const MALICIOUS_CONTENT_SEQUENCE_REV = '*H+H$!ELIF-TSET-SURIVITNA-DRADNATS-RACIE$}7)CC7)^P(45XZP\4[PA@%P!O5X';

		/**
		 * Gets a file content being detected as malicious content
		 * @return string
		 */
		protected function getMaliciousContentSequence() {
			return strrev(self::MALICIOUS_CONTENT_SEQUENCE_REV);
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

		public function testConstructorGetters() {

			$socket = 'unix:///not-a-socket';
			$timeout = 15;
			$bypass = true;

			$scanner = new VirusScanner($socket, $timeout, $bypass);

			$this->assertSame($socket, $scanner->getSocket());
			$this->assertSame($timeout, $scanner->getTimeout());
			$this->assertSame($bypass, $scanner->isBypass());

		}

		public function testScanFile() {

			$fileContent = 'hello world';

			$this->withFile($fileContent, function($path) {

				$scanner = $this->createVirusScanner();

				$this->assertSame($scanner, $scanner->scanFile($path));

			});

		}

		public function testScanFile_withMaliciousContent() {

			$fileContent = $this->getMaliciousContentSequence();

			$this->expectException(VirusDetectedException::class);

			$this->withFile($fileContent, function($path) {

				$scanner = $this->createVirusScanner();

				$scanner->scanFile($path);

			});

		}

		public function testScanFile_withMaliciousContent_bypassed() {

			$fileContent = $this->getMaliciousContentSequence();


			$this->withFile($fileContent, function($path) {

				$scanner = $this->createVirusScanner(30, true);

				$this->assertSame($scanner, $scanner->scanFile($path));

			});

		}

		public function testScanFile_notExisting() {

			$fileContent = 'Hello world';

			$this->expectException(VirusScanFailedException::class);

			$this->withFile($fileContent, function ($path) {

				$scanner = $this->createVirusScanner();

				$scanner->scanFile($path . '/notExisting');

			});

		}

		public function testScanFile_invalidSocket() {

			$fileContent = 'Hello world';

			$this->expectException(VirusScanFailedException::class);

			$this->withFile($fileContent, function ($path) {

				$scanner = $this->createVirusScanner(30, false, 'unix://tmp/invalidSocket.ctl');

				$scanner->scanFile($path);

			});

		}


	}