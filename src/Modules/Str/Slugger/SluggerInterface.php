<?php

namespace Gzhegow\Lib\Modules\Str\Slugger;


interface SluggerInterface
{
    public function translit(string $string, string $delimiter = null, string $locale = null) : ?string;

    public function slug(string $string, string $delimiter = null, string $locale = null) : ?string;
}
