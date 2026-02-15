<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Transformer
    |--------------------------------------------------------------------------
    |
    | The class that is used to transform your site files.
    | By Default this is the built in SiteTransformer
    | If you with to costomise you can provide
    | a class that descends from this.
    |
    */

    'blade_transformer' => \Tobya\WebflowSiteConverter\Transformers\SiteTransformer::class,

    'html_transformer' => \Tobya\WebflowSiteConverter\Transformers\HtmlTransformer::class,

    /*
    |--------------------------------------------------------------------------
    | Disks
    |--------------------------------------------------------------------------
    |
    | The transformation requires 3 seperate disk drivers.  The Input where
    | your site files are.  Output where the tranformed fiiles will be
    | by default this is the '/views' directory, and public where
    | all css, js and images files will be put.
    |
    */

    'disks' => [
        'input' => [  // disk name or array for build.

            'driver' => 'local',
            'root' => storage_path('/webflow-core'),
        ],

        'output' => [

            'driver' => 'local',
            'root' => resource_path('/views'),
        ],
        'public' =>  // Use root Public dir.
             [

                 'driver' => 'local',
                 'root' => public_path('/'),
             ],
    ],

    'sections' => [

        'dirs' => [

            'hashed' => 'extracted',

            'blades' => 'sections',
        ],

    ],

];
