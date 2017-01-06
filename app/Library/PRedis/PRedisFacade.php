<?php

namespace App\Library\PRedis;

use Illuminate\Support\Facades\Facade;

class PRedisFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'predis';
	}
}