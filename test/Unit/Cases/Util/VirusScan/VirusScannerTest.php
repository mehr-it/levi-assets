<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Util\VirusScan;


	use MehrIt\LeviAssets\Util\VirusScan\VirusDetectedException;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanFailedException;
	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use MehrItLeviAssetsTest\Helpers\TestsVirusScan;
	use MehrItLeviAssetsTest\Helpers\TestsWithFiles;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class VirusScannerTest extends TestCase
	{
		use TestsWithFiles;
		use TestsVirusScan;


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

		public function testScanStream() {

			$fileContent = 'hello world';

			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $fileContent);
			\Safe\rewind($resource);

			$scanner = $this->createVirusScanner();

			$this->assertSame($scanner, $scanner->scanStream($resource));

			$this->assertSame($fileContent, \Safe\stream_get_contents($resource));
		}

		public function testScanStream_noRewind() {

			$fileContent = 'hello world';

			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $fileContent);
			\Safe\rewind($resource);

			$scanner = $this->createVirusScanner();

			$this->assertSame($scanner, $scanner->scanStream($resource, false));

			$this->assertSame('', \Safe\stream_get_contents($resource));
		}

		public function testScanStream_withMaliciousContent() {

			$fileContent = $this->getMaliciousContentSequence();


			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $fileContent);
			\Safe\rewind($resource);

			$scanner = $this->createVirusScanner();

			try {
				$scanner->scanStream($resource);

				$this->fail('The expected exception was not thrown.');
			}
			catch (VirusDetectedException $ex) {
			}

			$this->assertSame($fileContent, \Safe\stream_get_contents($resource));

		}

		public function testScanStream_withMaliciousContent_noRewind() {

			$fileContent = $this->getMaliciousContentSequence();


			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $fileContent);
			\Safe\rewind($resource);

			$scanner = $this->createVirusScanner();

			try {
				$scanner->scanStream($resource, false);

				$this->fail('The expected exception was not thrown.');
			}
			catch (VirusDetectedException $ex) {
			}

			$this->assertSame('', \Safe\stream_get_contents($resource));

		}

		public function testScanStream_withMaliciousContent_bypassed() {

			$fileContent = $this->getMaliciousContentSequence();

			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $fileContent);
			\Safe\rewind($resource);

			$scanner = $this->createVirusScanner(30, true);

			$this->assertSame($scanner, $scanner->scanStream($resource));

			$this->assertSame($fileContent, \Safe\stream_get_contents($resource));

		}


		public function testScanStream_invalidSocket() {

			$fileContent = 'Hello world';

			$resource = \Safe\fopen('php://temp', 'w+');

			\Safe\fwrite($resource, $fileContent);
			\Safe\rewind($resource);

			$this->expectException(VirusScanFailedException::class);

			$scanner = $this->createVirusScanner(30, false, 'unix://tmp/invalidSocket.ctl');

			$scanner->scanStream($resource);

			$this->assertSame($fileContent, \Safe\stream_get_contents($resource));

		}


	}