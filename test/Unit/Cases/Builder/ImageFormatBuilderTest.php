<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use Imagine\Image\ImageInterface;
	use MehrIt\LeviAssets\Asset\ImageAsset;
	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\ImageFormatBuilder;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;
	use PHPUnit\Framework\MockObject\MockObject;

	class ImageFormatBuilderTest extends TestCase
	{

		public function testBuild() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageFormatBuilder();

			$options = ['jpg', 'quality=90'];

			$asset = $builder->build($asset, $options);

			$this->assertSame('jpg', $asset->getFormat());
			$this->assertSame(['quality' => '90'], $asset->getFormatOptions());
		}
		
		public function testBuild_withExt() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageFormatBuilder();

			$options = ['jpg', 'quality=90', 'ext=jpeg'];

			$asset = $builder->build($asset, $options);

			$this->assertSame('jpg', $asset->getFormat());
			$this->assertSame(['quality' => '90'], $asset->getFormatOptions());
		}

		public function testBuild_assocOptions() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageFormatBuilder();

			$options = ['jpg', 'quality' => 90];

			$asset = $builder->build($asset, $options);

			$this->assertSame('jpg', $asset->getFormat());
			$this->assertSame(['quality' => 90], $asset->getFormatOptions());
		}
		
		public function testBuild_assocOptionsWithExt() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageFormatBuilder();

			$options = ['jpg', 'quality' => 90, 'ext' => 'jpeg'];

			$asset = $builder->build($asset, $options);

			$this->assertSame('jpg', $asset->getFormat());
			$this->assertSame(['quality' => 90], $asset->getFormatOptions());
		}

		public function testBuild_withoutOptions() {

			/** @var ImageInterface|MockObject $image */
			$image = $this->getMockBuilder(ImageInterface::class)->getMock();

			$asset = new ImageAsset($image, [], []);

			$builder = new ImageFormatBuilder();

			$options = [];

			$asset = $builder->build($asset, $options);

			$this->assertSame('png', $asset->getFormat());
			$this->assertSame([], $asset->getFormatOptions());
		}

		public function testBuild_fromResourceAsset() {


			$asset = new ResourceAsset($this->resourceWithContent($this->png1Pix()), [], []);

			$builder = new ImageFormatBuilder();

			$options = [];

			/** @var ImageAsset $asset */
			$asset = $builder->build($asset, $options);

			$this->assertSame('png', $asset->getFormat());
			$this->assertSame([], $asset->getFormatOptions());
		}

		public function testProcessPath() {

			$paths = [
				'the/test/img.png' => 'the/test/img.jpg',
				'img.png'          => 'img.jpg',
				'img'              => 'img.jpg',
				'.img'             => '.img.jpg',
				'test/.img'        => 'test/.img.jpg',
			];

			$builder = new ImageFormatBuilder();

			foreach ($paths as $path => $expected) {
				$this->assertSame($builder, $builder->processPath($path, ['jpg']));

				$this->assertSame($expected, $path);
			}
		}
		
		public function testProcessPath_withExt() {

			$paths = [
				'the/test/img.png' => 'the/test/img.jpeg',
				'img.png'          => 'img.jpeg',
				'img'              => 'img.jpeg',
				'.img'             => '.img.jpeg',
				'test/.img'        => 'test/.img.jpeg',
			];

			$builder = new ImageFormatBuilder();

			foreach ($paths as $path => $expected) {
				$this->assertSame($builder, $builder->processPath($path, ['jpg', 'ext=jpeg' ]));

				$this->assertSame($expected, $path);
			}
		}
		
		public function testProcessPath_withExtAssoc() {

			$paths = [
				'the/test/img.png' => 'the/test/img.jpeg',
				'img.png'          => 'img.jpeg',
				'img'              => 'img.jpeg',
				'.img'             => '.img.jpeg',
				'test/.img'        => 'test/.img.jpeg',
			];

			$builder = new ImageFormatBuilder();

			foreach ($paths as $path => $expected) {
				$this->assertSame($builder, $builder->processPath($path, ['jpg', 'ext' => 'jpeg' ]));

				$this->assertSame($expected, $path);
			}
		}
		
		public function testProcessPath_withExtAssocFalse() {

			$paths = [
				'the/test/img.png' => 'the/test/img',
				'img.png'          => 'img',
				'img'              => 'img',
				'.img'             => '.img',
				'test/.img'        => 'test/.img',
			];

			$builder = new ImageFormatBuilder();

			foreach ($paths as $path => $expected) {
				$this->assertSame($builder, $builder->processPath($path, ['jpg', 'ext' => false]));

				$this->assertSame($expected, $path);
			}
		}

	}