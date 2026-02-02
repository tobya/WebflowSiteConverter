<?php

namespace Tobya\WebflowSiteConverter\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    public static function retrieveStorageDisk($storageConfig): Filesystem
    {
        if (is_array($storageConfig)) {
            return Storage::build($storageConfig);
        } else {
            return Storage::disk($storageConfig);
        }
    }
}
