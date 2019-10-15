<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Provider;


	use MehrIt\LeviAssets\Util\VirusScan\VirusScanner;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class LeviAssetsServiceProviderTest extends TestCase
	{

		public function testVirusScannerRegistration_withDefaultConfig() {

			/** @var VirusScanner $resolved */
			$resolved = app(VirusScanner::class);

			$this->assertInstanceOf(VirusScanner::class, $resolved);
			$this->assertSame($resolved, app(VirusScanner::class));

			$this->assertSame('unix:///var/run/clamav/clamd.ctl', $resolved->getSocket());
			$this->assertSame(30, $resolved->getTimeout());
			$this->assertSame(false, $resolved->isBypass());

		}

		public function testVirusScannerRegistration_withModifiedConfig() {

			config()->set('leviAssets.virusScan.socket', 'unix:///an/other/socket');
			config()->set('leviAssets.virusScan.timeout', '15');
			config()->set('leviAssets.virusScan.bypass', 'true');

			/** @var VirusScanner $resolved */
			$resolved = app(VirusScanner::class);


			$this->assertInstanceOf(VirusScanner::class, $resolved);
			$this->assertSame($resolved, app(VirusScanner::class));

			$this->assertSame('unix:///an/other/socket', $resolved->getSocket());
			$this->assertSame(15, $resolved->getTimeout());
			$this->assertSame(true, $resolved->isBypass());

		}

	}