<?php


	namespace MehrItLeviAssetsTest\Helpers;


	use MehrIt\LeviAssets\Contracts\AssetBuilder;
	use PHPUnit\Framework\MockObject\MockObject;

	trait MocksAssetBuilder
	{

		/**
		 * Mocks an asset builder
		 * @param callable|null $build
		 * @param callable|null $processPath
		 * @param callable|null $cleanup
		 * @return AssetBuilder
		 */
		protected function mockAssetBuilder(callable $build = null, callable $processPath = null, callable $cleanup = null): AssetBuilder {


			/** @var AssetBuilder|MockObject $builder */
			$builder = $this->getMockBuilder(AssetBuilder::class)->getMock();

			$builder
				->method('build')
				->willReturnCallback($build ?: function($resource) { return $resource; });

			$builder
				->method('processPath')
				->willReturnCallback(function(&$path, $options) use ($builder, $processPath) {

					if ($processPath)
						call_user_func_array($processPath, [
							&$path,
							$options
						]);

					return $builder;
				});

			$builder
				->method('cleanup')
				->willReturnCallback(function () use ($builder, $cleanup) {

					if ($cleanup)
						call_user_func($cleanup);

					return $builder;
				});

			return $builder;
		}

	}