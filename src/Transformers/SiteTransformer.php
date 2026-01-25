<?php

  namespace Tobya\WebflowSiteConverter\Transformers;
use Psr\Log\LogLevel;
use voku\helper\HtmlDomParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use voku\helper\SimpleHtmlDomInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Tobya\WebflowSiteConverter\Services\StorageService;


  class SiteTransformer
  {


    protected $timelock;

    protected Filesystem $st_wf_core;
    protected Filesystem $st_wf_output_public;
    protected Filesystem $st_wf_output_main;

    protected HtmlDomParser $doc;

    protected bool $move_images = true;

      /**
       * @param string $outputPath
       * @param $f
       * @return void
       */
      function processOtherFile(string $outputPath, $f): void
      {
          $this->log( "Copying file to: " . $this->st_wf_output_public->path($outputPath));
        // if (Str($f)->Contains(['.js', '.css', '.ttf', '.otf',
        //     '.svg', '.woff', '.woff2', '.jpeg',
        //     '.jpg', '.png', 'favicon.ico'])) {
        //     //  continue;
        //     //dd($outputPath);
                // Create Folder in output dir
              if (file_exists(pathinfo($this->st_wf_output_public->path($outputPath), PATHINFO_DIRNAME)) === false) {
                  mkdir(
                      pathinfo(
                          $this->st_wf_output_public->path($outputPath), PATHINFO_DIRNAME
                      ),
                      0777, true);
              }



                  $this->log( "Copying file to: " . $this->st_wf_output_public->path($outputPath) );

                    // copy file to public path
                  copy($this->st_wf_core->path($f),
                      $this->st_wf_output_public->path($outputPath));


              // echo "other file;\n";
              //    die();

        //  }
      }

      /**
       * @param string $outputPath
       * @param $f
       * @return void
       */
      function processHtmlFile(string $outputPath, $f): void
      {
          // Create directory if required.
          if (file_exists(pathinfo($this->st_wf_output_main->path($outputPath), PATHINFO_DIRNAME)) === false) {
               mkdir(
                  pathinfo(
                      $this->st_wf_output_main->path($outputPath), PATHINFO_DIRNAME
                  ),
                  0777, true);
          }


          $this->log( $this->st_wf_core->path($f));


          // Transform links and save to output dir
          $this->TransformLinks($this->st_wf_core->get($f), $f);

      }


    public function transform()
    {
        $this->doTransform();
    }

      /**
     * Execute the console command.
     */
    private function doTransform()
    {

        $this->st_wf_core = StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.input'));
        $this->st_wf_output_main =    StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.output'));
        $this->st_wf_output_public =  StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.public'));

       // $this->move_images = false;

        // get all files in webflow input directories.
        $allfiles = $this->st_wf_core->allFiles();


        collect($allfiles)->each(function($f)  {

            // the file will be output to same relative path.
            $outputPath = '/' .  $f;

            if (Str($f)->endsWith( ['.html','.htm'],true)) {

                $this->processHtmlFile($outputPath, $f);
                $this->extractSections($f);

                // save
                $this->st_wf_output_main->put(
                    $this->change_fileext($outputPath, 'blade.php'), $this->getHTMLFileContent()
                );
            } else {
                $this->processOtherFile($outputPath, $f);
            }

        });

    }


    public function TransformLinks( string $content, $path) {

            $this->doc = new HtmlDomParser($content);
            foreach($this->doc->find('link') as $l){
                $allAttributes = $l->getAllAttributes();
                echo $allAttributes['href'] . "\n";
                if (Str($allAttributes['href'])->startsWith(['/','http://','https://','#']) === false){
                    $l->href = '/'.Str($allAttributes['href'])->replace('../','');
                }

            }

            foreach($this->doc->find('a') as $l){
                $allAttributes = $l->getAllAttributes();

                if (isset($allAttributes['href']) === false){
                    continue;
                }

                if (Str($allAttributes['href'])->startsWith(['/','http://','https://','#']) === false){
                    $l->href = LinkTransformer::transform($allAttributes['href']);
                }

            }


            foreach($this->doc->find('script') as $l) {
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

            foreach($this->doc->find('img') as $l){
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

    }

    public function getHTMLFileContent()
    {
         return $this->doc->html();
    }

    public function extractSection($selector, string | callable $replacement, $path) {

        $this->log( "Extracting sections: $selector",[$replacement, $path]);
        $c = now()->format('iv');
        foreach($this->doc->find( $selector ) as $div){
            $this->log('Found section: ');
            $html = $div->innerhtml;
            $this->log($html);

            // Many sections on separate pages may be identical so hash to deduplicate.
            $hash = sha1($html);

            $this->st_wf_output_main->put("/extracted/{$selector}_extracted_{$hash}_$c.html",$html );

            // get replacement text
            if (is_callable($replacement)){
                $div->innerhtml = call_user_func($replacement, $html);
            } else {
                $div->innerhtml = $replacement;
            }
        }


    }

    /**
     * @param HtmlDomParser $this->>doc
     * @param array $markers
     * @param $replacement
     * @return void
     *
     * NOT CURRENTLY IN USE
     */
    public function extractSectionsByMarkers( array $markers, $replacement) {

        echo "Extracting sections: $class \n";
        $c = now()->format('iv');
                foreach($this->doc->find( $class ) as $div){
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


    public function log($value, $data = null, $level = LogLevel::DEBUG)
    {
        if (is_array($data)) {
            Log::log($level, $value, $data);
        }
        else {
            Log::log($level, $value);
        }

    }

    public function extractSections($filepath)
    {

    }






  }
