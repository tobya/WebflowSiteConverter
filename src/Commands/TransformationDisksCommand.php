<?php

namespace Tobya\WebflowSiteConverter\Commands;

use Illuminate\Console\Command;

class TransformationDisksCommand extends Command
{
    protected $signature = 'webflow:disks';

    protected $description = 'Show disks';

    public function handle(): void
    {
        print_r(config('webflow-site-converter.disks'));
    }
}
