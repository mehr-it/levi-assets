<?php


	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;


	use MehrIt\LeviAssets\Builder\ContentTypeBuilder;
	use MehrItLeviAssetsTest\Helpers\TestsWithFiles;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class ContentTypeBuilderTest extends TestCase
	{
		use TestsWithFiles;

		public function testBuild() {

			$res = $this->resourceWithContent('test text');

			$builder = new ContentTypeBuilder();

			$writeOptions = [];
			$options      = ['text/html'];

			$retRes = $builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Type' => 'text/html'], $writeOptions);

			$this->assertSame('test text', \Safe\stream_get_contents($retRes));

		}

		public function testBuild_multipleDirectives() {

			$res = $this->resourceWithContent('test text');

			$builder = new ContentTypeBuilder();

			$writeOptions = [];
			$options      = ['text/html', 'charset=UTF-8'];

			$retRes = $builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Type' => 'text/html; charset=UTF-8'], $writeOptions);

			$this->assertSame('test text', \Safe\stream_get_contents($retRes));

		}

		public function testBuild_noOptions_textAscii() {

			$res = $this->resourceWithContent('test text');

			$builder = new ContentTypeBuilder();

			$writeOptions = [];
			$options      = [];

			$retRes = $builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Type' => 'text/plain; charset=us-ascii'], $writeOptions);

			$this->assertSame('test text', \Safe\stream_get_contents($retRes));
		}

		public function testBuild_noOptions_textUft8() {

			$res = $this->resourceWithContent('test text für mich');

			$builder = new ContentTypeBuilder();

			$writeOptions = [];
			$options      = [];

			$retRes = $builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Type' => 'text/plain; charset=utf-8'], $writeOptions);

			$this->assertSame('test text für mich', \Safe\stream_get_contents($retRes));

		}

		public function testBuild_noOptions_html() {

			$res = $this->resourceWithContent('<html lang="en"><body></body></html>');

			$builder = new ContentTypeBuilder();

			$writeOptions = [];
			$options      = [];

			$retRes = $builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Type' => 'text/html; charset=us-ascii'], $writeOptions);

			$this->assertSame('<html lang="en"><body></body></html>', \Safe\stream_get_contents($retRes));
		}

		public function testBuild_noOptions_pdf() {

			$pdf = <<< EOF
JVBERi0xLjQKJcfsj6IKNSAwIG9iago8PC9MZW5ndGggNiAwIFIvRmlsdGVyIC9GbGF0ZURlY29k
ZT4+CnN0cmVhbQp4nL1cy3LcNhbd6yt6N+KUu02AAEh6F0/sJONMJnHkSVWcWbReLY2klmO14krW
+YP83izzDVl5MXjeBwiylW7V2FUqEgTxuPfcN9g/zuqFkLPa/U8XJzcHT1+3s9XdgW+evf4sXrxf
Hfx40C0a98830OuTm9nzI/tiP+tnR+cHwjeKWaMXfd/rWdu3C1OL2dHNweGsOvrPgagXjZwdfXlw
9Ne3h99WtRtKanl4Vs3V4YX7876aQ+usmsvDlWs9q3rXqAsdje4PN66jf+RfeVWJZmFMe3jvmtbu
z2ka4NLe2WemiQ+euBdmlRDu8eFdNTfuqX2XziQathZYkDTd1GJoa1wY3MelpVFOYeQnYS2ita/8
++jvjnB/cXRTKtHtOdIt7abVYdqMEMeMbH6/fl7f7ZeKbXWd+vo3A2+UahtKsThyJJZfM3uadsPI
WGj0U7y0f9w4fXd4C2S+cZ36vou8u8bRl5wVZN4buxE7Ti3c222J9HHB5+7Fj0XOvK7ck7TUE1zq
FVCJbcpxRlqUSWUBf3R68DbnJUJvBQ+QCxZ1ImxzBlclRODT66qzk+sm7g+RiG9g58A9WTci0clK
IuOeMuGWMSU9QTAiO3Dwr4N06ciSKw6jTWFnx1xiyJIWMGcA+zzSdB4VhSPsF44/nrMMNrqz83vI
1j3gxMrB9RhczzK4rplwMELI0OfwOzuXX3VrX3T0N31PqENR5JHpWXgeZHAM9ojW+zBvhi9dR+GN
4pWmRan+Z5W2QBdAdrbib3rELTNx8uv1YxKwX8CqUVcgvlYR9WJhuoT6hzAZsUpBjKMiarSikP9Q
Ce06MWJ7UCML3DoXdW0hVMKSXyliqWCDHq5Lf+I4p6R/Fwgc4VRUHij6RL1Gvg05fDrU2779k4rg
LczfmGhxcJyrkva9hsd+pCXwl+ATMUHAdFIhOoY65xTWtuL8XzEWhS7PI4Ci0+EBdMaFgIHRPvkd
jXdAVQLQKleyTLdRw88eMBsYcTYwhEzxfgvqbqhKU58i7NecINmwbxK4UcUSvi1zxG9gB7ewxkVB
1T6H1Z4F5QxDZgbkOF+dZ42UhDXb7cnQ8iRcNfa29boQlpg2MICn7hVh7BpePoMrbMOZoh2orYey
hGGucXXk0ir0trNaQni1oBqZTIzzqdxOmSfT6qGutW3UMWW6dsSxBH1AlO52HTKnAkcodZ9Go2gH
TQHORW5sPqtEN5zrd/ABi2ZqUfJAn74WIjj50li1asmne9Sp/qmwmli5HvPYRZqFjkb8paWjMLJx
Ws7RXTWd865To6dRrYVs03NlhFtz6nCcv++e3+HlCV7GUXt8316eWXz1vVBt4FOYKgwqvO+VrtZw
tYKrp/jK68pdycZ5i+kxmfuquDiyZBz+FK7CY9lYkK1Ka7+HxjVcnVZGeDhD7GVxCbIbmDKPTXOr
EnqZMSvEa5Zl0scVnsfSipZByxitXwRliIa862WRsSFwilpTiIJtYyb01PVZRu2TRkr2RqbIpwH3
J4UHywBtAtU44K8I2k+DPk3q7Txf31XEPLT+BmIWQ0A+3qtKRhF2U77Dxdzj5YZHfWToOY5N9/Qr
dQ2Y4DoCOA3VewlPE5yBVsY4AxXpy+KiPCOuQGFuiBG5BOV5S/vD5W+lYOCMEM6PcpLbHqLYFewm
PvWryazlS7pBDyvb55oYU6+uUpfjrDfi4ITG4KkPvpit5HiEWnC5ohMRq50acQM5NHHOu0AsK3CW
l01yejGGi+xkzs96IMrBMlFJVlp5V1bWetERV1Ysui4JbApWvC3yzl4KS2aDjIQzYhfJpOShAbEM                                                                                                                                                                                 
yeD4EW4hutkwVBe8UsiDjHilc8XtcxwuXwn6hRgRRfGGoC5x6grDpw0INLp297kR/DhqazNP9o66
EOF;


			$res = $this->resourceWithContent(base64_decode(str_replace("\n", '', $pdf)));

			$builder = new ContentTypeBuilder();

			$writeOptions = [];
			$options      = [];

			$retRes = $builder->build($res, $writeOptions, $options);

			$this->assertSame(['Content-Type' => 'application/pdf; charset=binary'], $writeOptions);

			$this->assertSame(base64_decode(str_replace("\n", '', $pdf)), \Safe\stream_get_contents($retRes));

		}
	}