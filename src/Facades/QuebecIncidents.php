<?php

namespace JeffersonGoncalves\QuebecIncidents\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\QuebecIncidents\QuebecIncidents
 */
class QuebecIncidents extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-quebec-incidents';
    }
}
