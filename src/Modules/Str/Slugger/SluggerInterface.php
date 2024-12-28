<?php

namespace Gzhegow\Lib\Modules\Str\Slugger;


interface SluggerInterface
{
    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return null|string
     */
    public function slug(string $string, string $delimiter = null, string $locale = null) : ?string;
}
