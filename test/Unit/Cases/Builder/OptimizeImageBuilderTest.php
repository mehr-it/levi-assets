<?php

	namespace MehrItLeviAssetsTest\Unit\Cases\Builder;

	use MehrIt\LeviAssets\Asset\ResourceAsset;
	use MehrIt\LeviAssets\Builder\OptimizeImageBuilder;
	use MehrIt\LeviImages\Optimization\Optimizer;
	use MehrItLeviAssetsTest\Unit\Cases\TestCase;

	class OptimizeImageBuilderTest extends TestCase
	{

		public function testBuild_withMock() {

			$asset = new ResourceAsset($this->resourceWithContent('input-content'), [], []);
			
			$optimizerMock = $this->mockAppSingleton(Optimizer::class);
			$optimizerMock
				->expects($this->once())
				->method('optimizeResource')
				->willReturnCallback(function($resource) {
					
					$this->assertSame('input-content', stream_get_contents($resource));
					
					
					return $this->resourceWithContent('output-content');
				});

			$builder = new OptimizeImageBuilder();

			$options = [];

			$asset = $builder->build($asset, $options);

			$this->assertSame('output-content', stream_get_contents($asset->asResource()));
			
		}		
		
	}