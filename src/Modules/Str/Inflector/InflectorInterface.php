<?php

namespace Gzhegow\Lib\Modules\Str\Inflector;


interface InflectorInterface
{
    public function pluralize(string $singular, ?int $limit = null, ?int $offset = null) : array;

    public function singularize(string $plural, ?int $limit = null, ?int $offset = null) : array;
}
