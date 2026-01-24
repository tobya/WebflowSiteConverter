<?php

namespace Tobya\WebflowSiteConverter\Commands;

use voku\helper\HtmlDomParser;
use Illuminate\Console\Command;
use voku\helper\SimpleHtmlDomInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Tobya\WebflowSiteConverter\Services\StorageService;
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

    protected $timelock;

    protected Filesystem $st_wf_core;
    protected Filesystem $st_wf_output_public;
    protected Filesystem $st_wf_output_main;

    protected bool $move_images = true;


    /**
     * Execute the console command.
     */
    public function handle()
    {


     //   $this->st_wf_core = Storage::disk('websitecore');
     //   $this->st_wf_output_main =    storage::disk('output');
     //   $this->st_wf_output_public =    storage::disk('public');

        $this->st_wf_core = StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.input'));
        $this->st_wf_output_main =    StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.output'));
        $this->st_wf_output_public =  StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.public'));

       // $this->move_images = false;

        $allfiles = $this->st_wf_core->allFiles();
       // $this->info(print_r($allfiles, true));
        //$this->timelock = 'a'; // now()->format('md-Hi');

        collect($allfiles)->each(function($f)  {

            $outputPath = '/' .  $f; //'/' . $this->timelock  . '/' .  $f;

            if (Str($f)->contains( ['.html'],true)) {

                if (file_exists(pathinfo($this->st_wf_output_main->path($outputPath),PATHINFO_DIRNAME)) === false){
                  echo "Creating folder: " .  pathinfo(
                          $this->st_wf_output_main->path($outputPath),PATHINFO_DIRNAME
                      ). "\n";
                  //die();
                  mkdir(
                      pathinfo(
                          $this->st_wf_output_main->path($outputPath),PATHINFO_DIRNAME
                      ),
                      0777, true);
                }


              //  copy($this->st_wf_core->path($f),
               //     $this->st_wf_output_main->path($this->change_fileext($outputPath,'.blade.php')));

                          echo  $f . "\n";
           echo $this->st_wf_core->path($f) . "\n";


           $content = $this->TransformLinks( $this->st_wf_core->get($f), $f);
           $output = $this->st_wf_output_main->put($this->change_fileext($outputPath,'blade.php'), $content);


            } else {
                echo "Copying file to: " . $this->st_wf_output_public->path($outputPath) . "\n";
                if (Str($f)->Contains(['.js','.css','.ttf','.otf',
                    '.svg','.woff','.woff2', '.jpeg',
                    '.jpg','.png','favicon.ico'])){
                  //  continue;
                //dd($outputPath);

                    if (file_exists(pathinfo($this->st_wf_output_public->path($outputPath),PATHINFO_DIRNAME)) === false){
                      echo "Creating folder: " .  pathinfo(
                              $this->st_wf_output_public->path($outputPath),PATHINFO_DIRNAME
                          ). "\n";
                      //die();
                      mkdir(
                          pathinfo(
                              $this->st_wf_output_public->path($outputPath),PATHINFO_DIRNAME
                          ),
                          0777, true);
                    }

                if ($this->move_images){

                    echo "Copying file to: " . $this->st_wf_output_public->path($outputPath) . "\n";
                copy($this->st_wf_core->path($f),
                    $this->st_wf_output_public->path($outputPath));
                }

                // echo "other file;\n";
                //    die();

            }
            }


// C:\Development\github\tests\WebflowConverter\resources\views/output\1223-1150/css/ballymaloe-cookery-school-2021.css


           echo "---\n";
        });
        echo "\n $this->timelock \n";
    }


    public function TransformLinks( string $content, $path) {

            $doc = new HtmlDomParser($content);
            foreach($doc->find('link') as $l){
                $allAttributes = $l->getAllAttributes();
                echo $allAttributes['href'] . "\n";
                if (Str($allAttributes['href'])->startsWith(['/','http://','https://']) === false){
                    $l->href = '/'.Str($allAttributes['href'])->replace('../','');
                }

            }

            foreach($doc->find('a') as $l){
                $allAttributes = $l->getAllAttributes();

                if (isset($allAttributes['href']) === false){
                    continue;
                }

                if (Str($allAttributes['href'])->startsWith(['/','http://','https://']) === false){
                    $l->href = LinkTransformer::transform($allAttributes['href']);
                }

            }


            foreach($doc->find('script') as $l) {
                $allAttributes = $l->getAllAttributes();

                if (isset($allAttributes['src']) === false) {
                    continue;
                }

                if (Str($allAttributes['src'])->startsWith(['/', 'http://', 'https://']) === false) {
                    $l->src = '/' . Str($allAttributes['src'])
                            ->replace('../', '');
                    // $l->src = "../noimage.jpg";
                }
            }

            foreach($doc->find('img') as $l){
                $allAttributes = $l->getAllAttributes();

                if (isset($allAttributes['src']) === false){
                    continue;
                }

                if (Str($allAttributes['src'])->startsWith(['/','http://','https://']) === false)
                {
                    $l->src = '/'.Str($allAttributes['src'])
                            ->replace('.html','')
                            ->replace('../','');
                   // $l->src = "../noimage.jpg";
                }

                if (isset($allAttributes['srcset']) !== false){


                    try{
//print_r(Str($allAttributes['srcset'])->explode(','));
                $srcset = Str($allAttributes['srcset'])->explode(',')->reduce(function( $line,$item){


                        return $line . ", @image($item)";

                });
                    } finally {
                    echo 'finally';
                    }
                $l->srcset = $srcset;
                   // dd($srcset);
                }


            }

            $this->extractSections($doc, '.garden-grid-section', '@include(\'garden-grid-section\')', $path);
            $this->extractSections($doc, '.section.footer', '@include(\'footer-section\')', $path);
            $this->extractSections($doc, 'title', '@include(\'title\')', $path);
            $this->extractSections($doc, '.navigation-fallen.w-nav', '@include(\'navigation-menu\')', $path);
            $this->extractSections($doc, '.mobile-navigation-fallen.w-nav', '@include(\'navigation-menu-mobile\')',$path);
            $this->extractSections($doc, '.container-11 .filter_grid-list', '@include(\'CourseListContainer11Gridlist\')',$path);
            $this->extractSections($doc, '.container-11 .filter_grid', '@include(\'CourseListContainer11FilterGrid\')',$path);
           // $this->extractSections($doc, 'head', '@include(\'headblock\')');

            return $doc->html();


    }

    public function extractSections(HtmlDomParser $doc, $class, $replacement, $path) {

        echo "Extracting sections: $class \n";
        $c = now()->format('iv');
                foreach($doc->find( $class ) as $div){
                $hash = sha1($div->innerhtml);
                $html = $div->innerhtml;
            //    $html =  "################# \n $path \n \n ############## \n " . $html;
                $this->st_wf_output_main->put("/extracted/{$class}_extracted_{$hash}_$c.html",$html );

                    $div->innerhtml = $replacement;
                }


    }

    /**
     * @param HtmlDomParser $doc
     * @param array $markers
     * @param $replacement
     * @return void
     *
     * NOT CURRENTLY IN USE
     */
    public function extractSectionsByMarkers(HtmlDomParser $doc, array $markers, $replacement) {

        echo "Extracting sections: $class \n";
        $c = now()->format('iv');
                foreach($doc->find( $class ) as $div){
                $hash = sha1($div->innerhtml);
                $this->st_wf_output_main->put("/extracted/{$class}_extracted_{$hash}_$c.html",$div->innerhtml );

                    $div->innerhtml = $replacement;
                }


    }

    public function change_fileext($filename, $new_extension) {
        $info = pathinfo($filename);

        echo $info['dirname']."/".$info['filename'] . '.' . $new_extension;
           return $info['dirname']."/".$info['filename'] . '.' . $new_extension;
    }
}
