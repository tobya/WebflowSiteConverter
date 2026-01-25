<?php

  namespace Tobya\WebflowSiteConverter\Transformers;

  class Transformer
  {


      public function   extractSections() abstract


      /**
       * Extract a section by
       * @param $html_doc
       * @param string $cssSelector
       * @param string|int|callable $replacement
       * @param string $file_path
       * @return string
       */
           public function extractSection($html_doc, string $cssSelector, string | int | callable $replacement, string $file_path   ) : string
           {

           }




  }
