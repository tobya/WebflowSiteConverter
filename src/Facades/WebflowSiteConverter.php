<?php

namespace Tobya\WebflowSiteConverter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tobya\WebflowSiteConverter\WebflowSiteConverter
 */
class WebflowSiteConverter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tobya\WebflowSiteConverter\WebflowSiteConverter::class;
    }
}
