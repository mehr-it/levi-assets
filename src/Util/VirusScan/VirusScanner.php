<?php


	namespace MehrIt\LeviAssets\Util\VirusScan;


	use Socket\Raw\Factory as SocketFactory;
	use Throwable;
	use Xenolope\Quahog\Client as ClamAvClient;
	use Xenolope\Quahog\Exception\ConnectionException;

	/**
	 * Scans files for malicious content
	 * @package MehrIt\LeviAssets\Util\VirusScan
	 */
	class VirusScanner
	{
		protected $bypass = false;

		protected $client;

		protected $socket;

		protected $timeout;


		/**
		 * Creates a new instance
		 * @param string $socket The socket to connect to ClamAV, eg. "unix:///var/run/clamav/clamd.ctl"
		 * @param int $timeout The virus scan timeout in seconds
		 * @param bool $bypass True if not to perform any virus scan
		 */
		public function __construct(string $socket, int $timeout = 30, bool $bypass = false) {
			$this->socket  = $socket;
			$this->timeout = $timeout;
			$this->bypass  = $bypass;
		}

		/**
		 * Gets if scanning is bypassed.
		 * @return bool True if scanning is bypassed. Else false.
		 */
		public function isBypass(): bool {
			return $this->bypass;
		}

		/**
		 * Gets the socket to use to connect to ClamAV
		 * @return string The socket
		 */
		public function getSocket(): string {
			return $this->socket;
		}

		/**
		 * Gets the scan timeout
		 * @return int The scan timeout
		 */
		public function getTimeout(): int {
			return $this->timeout;
		}


		/**
		 * Scans the given file for malicious content. If malicious content is detected or an error occurs, an exception is thrown.
		 * @param string $path The file
		 * @return VirusScanner
		 * @throws VirusDetectedException
		 * @throws VirusScanException
		 * @throws VirusScanFailedException
		 */
		public function scanFile(string $path) {

			// bypass virus scan
			if ($this->bypass)
				return $this;


			// open file stream
			try {
				$fp = \Safe\fopen($path, 'rb');
			}
			catch (Throwable $e) {
				throw new VirusScanFailedException("Failed to open \"{$path}\" for virus scan: {$e->getMessage()}");
			}

			try {
				$this->scanStream($fp, false);
			}
			finally {
				fclose($fp);
			}

			return $this;
		}

		/**
		 * Scans the given stream for malicious content. If malicious content is detected or an error occurs, an exception is thrown.
		 * @param resource $resource The stream
		 * @param bool $rewind True if to rewind the stream after scanning
		 * @return $this
		 * @throws VirusDetectedException
		 * @throws VirusScanException
		 * @throws VirusScanFailedException
		 * @throws \Safe\Exceptions\FilesystemException
		 */
		public function scanStream($resource, bool $rewind = true) {

			// bypass virus scan
			if ($this->bypass)
				return $this;

			// scan file
			$status = null;
			try {
				/** @var array $result */
				$result = $this->getClient()->scanResourceStream($resource);
				$status = $result['status'] ?? null;
			}
				/** @noinspection PhpRedundantCatchClauseInspection */
			catch (ConnectionException $ex) {
				throw new VirusScanFailedException("Failed to connect to ClamAV ({$this->socket})", 0, $ex);
			}
			catch (Throwable $ex) {
				throw new VirusScanFailedException("Unexpected error on virus scan: ({$ex->getMessage()})", 0, $ex);
			}
			finally {
				if ($rewind)
					\Safe\rewind($resource);
			}


			// evaluate result
			switch ($status) {
				case ClamAvClient::RESULT_ERROR:
					$reason = $result['reason'] ?? null;
					throw new VirusScanFailedException("ClamAV scanner failed to scan stream with error \"{$reason}\" ({$status})");

				case ClamAvClient::RESULT_FOUND:
					$reason = $result['reason'] ?? null;
					throw new VirusDetectedException("Malicious content ({$reason}) detected");

				case ClamAvClient::RESULT_OK:
					break;

				default:
					throw new VirusScanException("Virus scan returned unexpected status \"{$status}\".");
			}

			return $this;

		}

		/**
		 * Creates or returns the client instance
		 * @return ClamAvClient
		 */
		protected function getClient() : ClamAvClient {

			if (!$this->client) {
				$socket = (new SocketFactory())->createClient($this->socket);

				$this->client = new ClamAvClient($socket, $this->timeout, PHP_NORMAL_READ);
			}

			return $this->client;
		}


	}