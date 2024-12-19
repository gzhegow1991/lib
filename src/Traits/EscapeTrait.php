<?php

namespace Gzhegow\Lib\Traits;

trait EscapeTrait
{
    public static function escape_sql_like(string $like) : string
    {
        $search = [ '%', '_' ];
        $replace = [ '\%', '\_' ];

        $escape = str_replace($search, $replace, $like);

        return $escape;
    }
}
