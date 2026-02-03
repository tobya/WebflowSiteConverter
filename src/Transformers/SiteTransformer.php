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

    public $extractions = [];
    public $replacements = [];

    public $debug = false;

    protected $current_filename;

    protected $view_file_ext = '.blade.php';


      public bool $create_section_files = false;
      public bool $overwrite_section_files = false;

      /**
       * @param string $outputPath
       * @param $f
       * @return void
       */
      function processOtherFile(string $outputPath, $f): void
      {
          $this->log( "Copying file to: " . $this->st_wf_output_public->path($outputPath));


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

       $this->retrieveDisks();

       // $this->move_images = false;

        // get all files in webflow input directories.
        $allfiles = $this->st_wf_core->allFiles();


        collect($allfiles)->each(function($f)  {

            $this->current_filename = $f;
            // the file will be output to same relative path.
            $outputPath = '/' .  $f;

            if (Str($f)->endsWith( ['.html','.htm'],true)) {

                $this->processHtmlFile($outputPath, $f);
                $this->processReplacements();
                $this->extractAllSections($f);

                // save
                $this->st_wf_output_main->put(
                    $this->change_fileext($outputPath, $this->view_file_ext), $this->getHTMLFileContent()
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



                $srcset = Str($allAttributes['srcset'])->explode(',')->reduce(
                        function( $line,$item){

                            $item = '/' .  Str($item)
                                    ->replace('../../../','',false)
                                    ->replace('../../','',false)
                                    ->replace('../','',false);

                            return $line . ", @image(\"$item\")";

                        }
                );

                $l->srcset = $srcset;
                   // dd($srcset);
                }


            }

    }

    public function getHTMLFileContent()
    {
         return $this->doc->html();
    }

    public function extractSectionAsBlade($selector, callable | null $content = null)
    {


        $this->log( "Extracting section as Blade: $selector");

        foreach($this->doc->find( $selector ) as $div){
            $this->log('Found section: ');
            $html = $div->innerhtml;
            $this->log($html);

            // Many sections on separate pages may be identical so hash to deduplicate.
            $hash = sha1($html);



            /**
             * Just testing here at the moment.
             * Wondering what to do about creating sections.  Currently including name of fn as a sub folder.
             * this creates a seperate file for each section from each file.
             * This would help to create a set of files that could be changed.
             *
             * or could go back to just creating first.
             */
            // store section
            $this->st_wf_output_main->put("/extracted/{$selector}_extracted_{$hash}.html",   $html );
            $this->st_wf_output_main->put("/extracted/{$selector}_extracted_{$hash}.html",   $html );


             $safename = Str($selector )->slug('') . '_' . Str($hash)->substr(0,10);
             $safefn = Str($this->current_filename)->slug('');
             $this->st_wf_output_main->put("/sections/{$safename}{$this->view_file_ext}", $html );
            //  $this->st_wf_output_main->put("/sections/{$safename}.{$this->current_filename} .blade.php", $this->current_filename . "\n\n\nafdasfd" . $html,[] );



            // get replacement text
            if (is_callable($content)){
                $div->innerhtml = call_user_func($content, $html);
            } else {
                $div->innerhtml = " @include(\"" . config('webflow-site-converter.sections.dirs.blades') . ".{$safename}\") ";
            }
        }
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

            $this->st_wf_output_main->put("/extracted/{$selector}_extracted_{$hash}.html",$html );




            // get replacement text
            if (is_callable($replacement)){
                $div->innerhtml = call_user_func($replacement, $html);
            } else {
                $div->innerhtml = $replacement;
            }
        }


    }



    public function change_fileext($filename, $new_extension) {

        if (Str($new_extension)->startsWith('.')){
            $new_extension = substr($new_extension, 1);
        }

        $info = pathinfo($filename);

        return $info['dirname']."/".$info['filename'] . '.' . $new_extension;
    }


    public function log($value, $data = null, $level = LogLevel::DEBUG)
    {
        if ($this->debug == false){
            return ;
        }

        if (is_array($data)) {
            Log::log($level, $value, $data);
        }
        else {
            Log::log($level, $value);
        }

    }

    private function extractAllSections($filepath)
    {
        foreach($this->extractions as $extraction){
            $this->extractSection($extraction[0], $extraction[1], $filepath);
        }
        $this->ExtractSections($filepath);

    }

    public function ExtractSections($filepath)
    {


    }

      /**
       * Retrieve all disks
       * @return void
       */
      protected function retrieveDisks()
      {

            $this->st_wf_core = $this->st_wf_core ?? StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.input'));
            $this->st_wf_output_main =  $this->st_wf_output_main ??   StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.output'));
            $this->st_wf_output_public = $this->st_wf_output_public ??  StorageService::retrieveStorageDisk(config('webflow-site-converter.disks.public'));

      }


      protected function processReplacements()
      {
          foreach($this->replacements as $replacement){

             list($selector, $find, $replace) = $replacement;
              //  echo "\n ----------------\n \n $selector \n \n \n \n";
             $elements = $this->doc->find($selector);
             foreach($elements as $element){

                 $html = $element->outertext() ;

                // echo "\n\n :::::::::::::::::::: \n\n";
               //  echo $html;
                 $strHtml = Str($html);
                 if ($strHtml->contains($find)){
                     $html = $strHtml->replace($find, $replace, false)->toString();
                   //  echo "\n\n --- \n\n";
                   //  echo $html;
                   //  echo "\n\n :::::::::::::::::::: \n\n";
                     $element->outertext = $html;
                 }
             }

          }
      }

  }
