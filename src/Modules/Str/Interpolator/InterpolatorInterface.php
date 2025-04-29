<?php

namespace Gzhegow\Lib\Modules\Str\Interpolator;


interface InterpolatorInterface
{
    public function interpolate(string $string, array $placeholders = []) : string;

    /**
     * @param string|array|\stdClass $message
     *
     * @return string
     */
    public function interpolateMessage($message) : string;
}
