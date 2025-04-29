<?php

namespace Gzhegow\Lib\Modules\Str\Interpolator;


class DefaultInterpolator implements InterpolatorInterface
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

    /**
     * @param string|array|\stdClass $message
     *
     * @return string
     */
    public function interpolateMessage($message) : string
    {
        $messageArray = (array) $message;
        $messageString = $messageArray[ 'message' ] ?? $messageArray[ 0 ] ?? '';

        $replacements = [];
        foreach ( $messageArray as $key => $value ) {
            $replacements[ "{{" . $key . "}}" ] = $value;
        }

        $result = strtr($messageString, $replacements);

        return $result;
    }
}
