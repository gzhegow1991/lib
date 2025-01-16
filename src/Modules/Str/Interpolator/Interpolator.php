<?php

namespace Gzhegow\Lib\Modules\Str\Interpolator;


class Interpolator implements InterpolatorInterface
{
    public function interpolate(string $string, array $placeholders = []) : string
    {
        $replacements = [];
        foreach ( $placeholders as $key => $value ) {
            $replacements[ "{{" . $key . "}}" ] = $value;
        }

        $result = strtr($string, $replacements);

        return $result;
    }
}
