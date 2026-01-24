<?php

// config for Tobya/WebflowSiteConverter
return [





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
