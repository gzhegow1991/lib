<?php

namespace Gzhegow\Lib\Modules;

class EscapeModule
{
    public function sql_like(string $like) : string
    {
        $search = [ '%', '_' ];
        $replace = [ '\%', '\_' ];

        $escape = str_replace($search, $replace, $like);

        return $escape;
    }
}
