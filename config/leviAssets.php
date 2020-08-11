<?php
	return [

		/*
		 * Configures the virus scan options
		 */
		'virusScan'        => [
			/*
			 * True to bypass the virus scan
			 */
			'bypass'  => env('ASSETS_VIRUS_SCAN_BYPASS') ? true : false,
			/*
			 * The timeout for scanning a file in seconds
			 */
			'timeout' => env('ASSETS_VIRUS_SCAN_TIMEOUT', 30),
			/*
			 * Sets the socket for the communication with clamd
			 */
			'socket'  => env('ASSETS_VIRUS_SCAN_SOCKET', 'unix:///var/run/clamav/clamd.ctl'),
		],

		/*
		 * Defines asset collections
		 */
		'collections' => [

		],

		/*
		 * Defines the asset builders
		 */
		'builders' => [
			'cache'       => \MehrIt\LeviAssets\Builder\CacheControlBuilder::class,
			'disposition' => \MehrIt\LeviAssets\Builder\ContentDispositionBuilder::class,
			'encoding'    => \MehrIt\LeviAssets\Builder\ContentEncodingBuilder::class,
			'language'    => \MehrIt\LeviAssets\Builder\ContentLanguageBuilder::class,
			'mime'        => \MehrIt\LeviAssets\Builder\ContentTypeBuilder::class,
			'expires'     => \MehrIt\LeviAssets\Builder\ExpiresBuilder::class,
			's3'          => \MehrIt\LeviAssets\Builder\S3OptionsBuilder::class,
		],

		/*
		 * Configures the asset linker
		 */
		'links' => [

			/*
			 * Configures the filters to apply to all links
			 */
			'default_filters' => [

				/*
				 * force HTTPS links
				 */
				['proto', 'https'],
			],

			/*
			 * Defines custom filters
			 */
			'filters' => [

			],
		]

	];