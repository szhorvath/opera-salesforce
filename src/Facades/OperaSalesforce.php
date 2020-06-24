<?php

namespace Szhorvath\OperaSalesforce\Facades;

use Illuminate\Support\Facades\Facade;

class OperaSalesforce extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'opera-salesforce';
    }
}
