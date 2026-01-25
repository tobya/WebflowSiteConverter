<?php

namespace Tobya\WebflowSiteConverter\Commands;

use voku\helper\HtmlDomParser;
use Illuminate\Console\Command;
use voku\helper\SimpleHtmlDomInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Tobya\WebflowSiteConverter\Services\StorageService;
use Tobya\WebflowSiteConverter\Transformers\SiteTransformer;
use Tobya\WebflowSiteConverter\Transformers\URLTransformer;
use Tobya\WebflowSiteConverter\Transformers\LinkTransformer;


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
