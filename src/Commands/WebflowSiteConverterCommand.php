<?php

namespace Tobya\WebflowSiteConverter\Commands;

use Illuminate\Console\Command;

class WebflowSiteConverterCommand extends Command
{
    public $signature = 'webflowsiteconverter';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
