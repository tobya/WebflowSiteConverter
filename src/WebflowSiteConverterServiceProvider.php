<?php

namespace Tobya\WebflowSiteConverter;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tobya\WebflowSiteConverter\Commands\TransformationDisksCommand;
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
            ->name('webflow-site-converter')
            ->hasConfigFile()
          // ->hasViews()
            // ->hasMigration('create_webflowsiteconverter_table')
            ->hasCommand(TransformLinks::class)
            ->hasCommand(TransformationDisksCommand::class);

    }

    public function packageRegistered()
    {

        Route::macro('webflow', function () {
            Route::get('/{any}', function ($any) {
                if (View::exists($any)) {
                    return view($any, []);
                } else {
                    abort(404);
                }
            })->where('any', '.*')->name('any');
        });

    }
}
