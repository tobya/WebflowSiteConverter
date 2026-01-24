<?php

  namespace Tobya\WebflowSiteConverter\Interfaces;

  interface Extractor
  {

      public function extract($html_doc, string $cssSelector, string | int | callable $replacement, string $file_path   ) : string;

  }
