<?php

namespace Tobya\WebflowSiteConverter\Commands;

use Illuminate\Console\Command;

class TransformLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webflow:transform';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean webflow output';

    public function handle()
    {
        $transformer = app(config('webflow-site-converter.transformer'));
        $transformer->transform();
    }
}
