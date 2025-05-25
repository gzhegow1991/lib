<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Exception\RuntimeException;


class EscapeModule
{
    public function sql_in(
        ?array &$refPdoParams,
        string $sql, array $in, ?string $paramNamePrefix = null
    ) : string
    {
        $refPdoParams = $refPdoParams ?? [];

        if ([] === $in) {
            return '';
        }

        $paramNamePrefix = (string) $paramNamePrefix;

        $hasParamNamePrefix = ('' !== $paramNamePrefix);

        $i = 0;
        $sqlIn = '';
        foreach ( $in as $value ) {
            if ($hasParamNamePrefix) {
                $pdoParamName = ":{$paramNamePrefix}{$i}";

                if (isset($refPdoParams[ $pdoParamName ])) {
                    throw new RuntimeException(
                        [ 'The `params` already has parameter named: ' . $pdoParamName, $refPdoParams ]
                    );
                }

                $refPdoParams[ $pdoParamName ] = $value;

                $sqlIn .= "{$pdoParamName}, ";

            } else {
                $refPdoParams[] = $value;

                $sqlIn .= "?, ";
            }

            $i++;
        }
        $sqlIn = rtrim($sqlIn, ', ');
        $sqlIn = "{$sql} IN ({$sqlIn})";

        return $sqlIn;
    }


    public function sql_like_escape(string $sql, string $like = 'LIKE', ...$valueParts)
    {
        if ([] === $valueParts) {
            return '';
        }

        $value = '';
        foreach ( $valueParts as $v ) {
            $value .= is_array($v)
                ? $v[ 0 ]
                : $this->sql_like_quote($v);
        }

        $result = "{$sql} {$like} \"{$value}\"";

        return $result;
    }

    public function sql_like_quote(string $string, ?string $escaper = null) : string
    {
        $escaper = $escaper ?? '\\';

        $search = [ '%', '_' ];
        $replace = [ "{$escaper}%", "{$escaper}_" ];

        $result = str_replace($search, $replace, $string);

        return $result;
    }
}
