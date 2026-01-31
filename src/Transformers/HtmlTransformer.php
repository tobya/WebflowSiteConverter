<?php

  namespace Tobya\WebflowSiteConverter\Transformers;

  use Tobya\WebflowSiteConverter\Services\StorageService;
  use Tobya\WebflowSiteConverter\Transformers\SiteTransformer;

  class HtmlTransformer extends SiteTransformer
  {

        public function __construct()
        {
            $this->view_file_ext = '.html';
            $this->retrieveDisks();
            // output all files to public disk.
            $this->st_wf_output_main = $this->st_wf_output_public;
        }


  }
