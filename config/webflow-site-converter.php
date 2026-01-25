<?php

// config for Tobya/WebflowSiteConverter
return [


    'transformer' => \Tobya\WebflowSiteConverter\Transformers\SiteTransformer::class,


    'sections' => [

        'extracted-sections-hashed-directory' => 'extracted',

        'extracted-sections-blades' => 'sections',
    ],


    'disks' => [
        'input' => [  // disk name or array for build.

            'driver' => 'local',
            'root' => storage_path('/webflow-core'),
            ] ,

        'output' => [

            'driver' => 'local',
            'root' => resource_path('transformed'),
            ] ,
        'public' => 'public', // Use Public disk.
        ],


];
