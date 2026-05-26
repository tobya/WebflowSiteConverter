<?php

namespace Tobya\WebflowSiteConverter\Transformers;

use Illuminate\Support\Stringable;

class LinkTransformer extends Transformer
{
    public static function transform(string $linkURL, $replacement_url_fileext = ''): Stringable
    {
        $urlTransform = URLTransformer::transform($linkURL);

        return $urlTransform->replace('.html', $replacement_url_fileext);
    }
}
