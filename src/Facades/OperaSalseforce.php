<?php

namespace Szhorvath\OperaSalseforce\Facades;

use Illuminate\Support\Facades\Facade;

class OperaSalseforce extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'opera-salseforce';
    }
}
