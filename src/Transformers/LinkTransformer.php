<?php

namespace Tobya\WebflowSiteConverter\Transformers;

use Illuminate\Support\Stringable;

class LinkTransformer extends Transformer
{
    public static function transform(string $linkURL): Stringable
    {
        $urlTransform = URLTransformer::transform($linkURL);

        return $urlTransform->replace('.html', '');
    }
}
