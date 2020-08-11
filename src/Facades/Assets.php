<?php


	namespace MehrIt\LeviAssets\Facades;


	use Illuminate\Support\Facades\Facade;
	use MehrIt\LeviAssets\AssetsManager;

	class Assets extends Facade
	{

		/**
		 * Get the registered name of the component.
		 *
		 * @return string
		 */
		protected static function getFacadeAccessor() {
			return AssetsManager::class;
		}

	}