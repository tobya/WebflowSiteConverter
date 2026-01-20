<?php

namespace Tobya\WebflowSiteConverter;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tobya\WebflowSiteConverter\Commands\TransformLinks;

class WebflowSiteConverterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('webflowsiteconverter')
            ->hasConfigFile()
            ->hasViews()
            // ->hasMigration('create_webflowsiteconverter_table')
            ->hasCommand(TransformLinks::class);
    }
}
