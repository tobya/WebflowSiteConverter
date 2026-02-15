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
        if ($this->option('html') == true) {
            $transformer = app(config('webflow-site-converter.html_transformer'));
            $transformer->transform();
            $this->info('HTML Transformer has copied .html files to public directory along with all css, js, and image files
            Most likely you can acces the site on [site.com]/index.html');
        } else {
            $transformer = app(config('webflow-site-converter.blade_transformer'));
            $transformer->transform();
            $this->info('Blade Transformer transforms .html files to views directory along with copying  all css, js, and image files
            Most likely you can acces the site on [site.com]/index

            Ensure your routes file (web.php) has

            Route::webflow();');
        }

    }
}
