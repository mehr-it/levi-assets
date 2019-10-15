<?php


	namespace MehrItLeviAssetsTest\Helpers;


	trait TestsWithFiles
	{

		/**
		 * Executes the given callback with the given content written to a file
		 * @param string $content The file content
		 * @param callable $callback The callback. Receives the file name as parameter
		 * @return mixed The callback return
		 * @throws \Safe\Exceptions\FilesystemException
		 */
		protected function withFile(string $content, callable $callback) {

			$fn = \Safe\tempnam(sys_get_temp_dir(), 'LeviAssetsTest');
			\Safe\file_put_contents($fn, $content);

			try {
				return call_user_func($callback, $fn);
			}
			finally {
				if (file_exists($fn))
					@unlink($fn);
			}
		}

	}