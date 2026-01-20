<?php

namespace Tobya\WebflowSiteConverter\Transformers;

use Illuminate\Support\Stringable;

class URLTransformer extends Transformer
{
    public static function transform(string $url): Stringable
    {
        return Str('/789/'.$url)->replace('../', '');
    }
}
