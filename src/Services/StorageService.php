<?php

  namespace Tobya\WebflowSiteConverter\Services;

  use Illuminate\Support\Facades\Storage;
  use Illuminate\Contracts\Filesystem\Filesystem;

  class StorageService
  {
        public static function retrieveStorageDisk($storageConfig) : Filesystem
        {
            if (is_array($storageConfig)){
                return Storage::build($storageConfig);
            } else {
                return Storage::disk($storageConfig);
            }
        }
  }
