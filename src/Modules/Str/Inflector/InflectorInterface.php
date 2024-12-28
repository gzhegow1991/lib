<?php

namespace Gzhegow\Lib\Modules\Str\Inflector;


interface InflectorInterface
{
    /**
     * @param string   $singular
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return null|array
     */
    public function pluralize(string $singular, int $limit = null, int $offset = null) : array;

    /**
     * @param string   $plural
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return null|array
     */
    public function singularize(string $plural, int $limit = null, int $offset = null) : array;
}
