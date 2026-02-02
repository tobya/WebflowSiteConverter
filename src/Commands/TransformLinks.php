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
    protected $signature = 'webflow:transform {--html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean webflow output';

    public function handle()
    {
        if($this->option('html') == true) {
            $transformer = app(config('webflow-site-converter.html_transformer'));
        } else {
            $transformer = app(config('webflow-site-converter.blade_transformer'));
        }

        $transformer->transform();
    }
}
