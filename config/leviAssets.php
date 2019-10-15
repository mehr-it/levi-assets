<?php
	return [

		// configure the virus scan
		'virusScan'        => [
			/*
			 * True to bypass the virus scan
			 */
			'bypass'  => env('ASSETS_VIRUS_SCAN_BYPASS') ? true : false,
			/*
			 * The timeout for scanning a file in seconds
			 */
			'timeout' => env('ASSETS_VIRUS_SCAN_TIMEOUT', 30),
			/**
			 * Sets the socket for the communication with clamd
			 */
			'socket'  => env('ASSETS_VIRUS_SCAN_SOCKET', 'unix:///var/run/clamav/clamd.ctl'),
		],

	];