<?php


	namespace MehrIt\LeviAssets\Builder;


	use Illuminate\Support\Arr;
	use Illuminate\Support\Str;

	class S3OptionsBuilder extends AbstractAssetBuilder
	{
		/**
		 * @inheritDoc
		 */
		public function build($resource, &$writeOptions = [], array $options = []) {

			// extract options which can be passed to s3
			$s3Options = Arr::only(
				array_change_key_case($writeOptions, CASE_LOWER),
				[
					'cache-control',
					'content-disposition',
					'content-encoding',
					'content-language',
					'content-type',
					'expires',
				]
			);

			// rebuild write options in studly case as wanted by s3 client
			$writeOptions = [];
			foreach($s3Options as $key => $value) {
				$writeOptions[Str::studly($key)] = $value;
			}

			return $resource;
		}


	}