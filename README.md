# Convert exported Webflow Site to a usable blade site

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tobya/webflowsiteconverter.svg?style=flat-square)](https://packagist.org/packages/tobya/webflowsiteconverter)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/tobya/webflowsiteconverter/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tobya/webflowsiteconverter/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/tobya/webflowsiteconverter/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/tobya/webflowsiteconverter/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/tobya/webflowsiteconverter.svg?style=flat-square)](https://packagist.org/packages/tobya/webflowsiteconverter)

Webflow is a no code (or low code) generator site which is really amazing from a design point of view and 
allows you to really rapidly generate wonderful looking sites.

However, if it is a very small, or simple site then webflow monthly fees can get a bit out of hand. 
Webflow provide a simple way to export your html, css and js from their system. I have 
previously tried to export this zip file and view it. It works fine as a static local
html site but has various issues if you wish to transform it into a blade views for a laravel site.

This project will take a set of files from an exported webflow project and do the following

- Convert all `.html` files to `blade.php` files
- Copy all other files (.js .css .jpg .jpeg .png .ttf etc) to your public directory for loading
- Convert all `href` and other urls from relative eg `about.html` to fixed routes `/about`
- Convert all script tags to fixed uris pointing to the copy in your public directory
- Convert all css link tags to fixed uris pointing to the copy in your public directory
- Convert all relative # tags to fixed on the route eg `about.html#Contact` to `/about#Contact`
- Convert simpley HTML site. Will simply import html files, css, js and images to public directory.  will rewrite all relative urls to start with '/'

### In Development

This project is  still in active development and may have (quite) a few rough edges.  I am adding features as required for the few sites I use it on.  Delighted with an help, PRs , Issues, discussions.

### Additional Options
- You can extract a section via a css selector and replace with a snippet

This will pull out the innerhtml from a tag with the container1 class and replace
it with the include allowing you to put your own content in these sections.  Additionally
these are all extracted to seperate files so you can check changes.

````php
    $this->extractsection('.container1',"@include('containers.main')");
````

### Route

If you have a simple site that only needs the pages to be linked up correctly, you
can call a simple route function and your site will work out of the box.

> Route::webflow();

For anything more complex you can create routes and controllers as you normally would.


### Rerunning

This project has also been designed to allow you to export your data multiple times.
As the project is overwritten you can diff the changes and update.  Thsi is 


## Support us

Delighed with any sponsors

## Installation

You can install the package via composer:

```bash
composer require tobya/webflowsiteconverter
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="webflow-site-converter-config"
```

This is the contents of the published config file:

```php
<?php

// config for Tobya/WebflowSiteConverter
return [


  
    'transformer' => \Tobya\WebflowSiteConverter\Transformers\SiteTransformer::class,


    'disks' => [
        'input' => [  // disk name or array for build.

            'driver' => 'local',
            'root' => storage_path('/webflow-core'),
            ] ,

        'output' => [

            'driver' => 'local',
            'root' => resource_path('transformed'),
            ] ,
        'public' => 
              [
            'driver' => 'local',
            'root' => public_path('/'),
            ] ,        
        ],


];

```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="webflowsiteconverter-views"
```

## Usage

````bash
php artisan webflow:transform
````

or to use for a simple html site

````bash
php artisan webflow:transform --html
````

## Routes

Once you have transformed your webflow site, you can create routes and controllers
as normal using the new blade components.

If you wish to have a site up and running immediately, you can use a single route that 
will catch all non registered routes

`````php

Route::webflow();

`````

This will serve your new blade templates directly.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Toby Allen](https://github.com/tobya)
- [All Contributors](../../contributors)
- Webflow

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Webflow

'Webflow' is a registered Trademark of Webflow Inc.  
