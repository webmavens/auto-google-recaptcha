<?php

namespace WebMavens\AutoGoogleRecaptcha\Facades;

use Illuminate\Support\Facades\Facade;

class AutoReCaptcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'autorecaptcha';
    }
}