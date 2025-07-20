<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;


class MbModule
{
    public function __construct()
    {
    }

    /**
     * @return static
     */
    public function assertExtension()
    {
        if (! extension_loaded('mbstring')) {
            throw new ExtensionException(
                'Missing PHP extension: mbstring'
            );
        }

        return $this;
    }


    /**
     * @param array|null $detect_order
     *
     * @return array{ 0: string[], 1: array<string, string[]> }
     */
    public function list_encodings(?array $detect_order = []) : array
    {
        static $cache;

        if (null === $detect_order) {
            $detect_order = [
                'utf32',
                'utf16',
                'utf8',
                'utf7',
                'ascii',
                'windows',
                'cp',
                'iso',
                'koi8',
                'sjis',
                'jis',
                'eucjp',
                'euc',
                'ucs',
            ];

        } elseif ([] === $detect_order) {
            foreach ( explode(',', mb_detect_order()) as $encGroup ) {
                $detect_order[] = $encGroup;
            }
        }

        $thePhp = Lib::$php;

        $currentEncGroupPriority = 0;
        $groupsIndexPrioritized = [];
        foreach ( $detect_order as $encGroup ) {
            $encGroupString = trim($encGroup);
            $encGroupString = strtolower($encGroupString);
            $encGroupString = str_replace([ ' ', '-' ], '', $encGroupString);

            $groupsIndexPrioritized[ $encGroupString ] = ++$currentEncGroupPriority;
        }

        $cacheKey = crc32(serialize($groupsIndexPrioritized));

        if (! isset($cache[ $cacheKey ])) {
            $encsList = mb_list_encodings();

            if ($thePhp->is_windows()) {
                $encsList[] = 'CP1251';
                $encsList[] = 'Windows-1251';
            }

            $remove = [];
            if (PHP_VERSION_ID < 82000) {
                $remove = [
                    '7bit'            => true,
                    '8bit'            => true,
                    'base64'          => true,
                    'htmlentities'    => true,
                    'qprint'          => true,
                    'quotedprintable' => true,
                    'uuencode'        => true,
                ];
            }

            $encsIndexed = [];
            foreach ( $encsList as $enc ) {
                $encString = trim($enc);
                $encString = strtolower($encString);
                $encString = str_replace([ ' ', '-' ], '', $encString);

                if (! isset($remove[ $encString ])) {
                    $encsIndexed[ $encString ] = $enc;
                }
            }

            $encsOrder = [];
            foreach ( $encsIndexed as $encString => $enc ) {
                $currentEncGroup = null;
                $currentEncGroupPriority = null;

                foreach ( $groupsIndexPrioritized as $encGroup => $encGroupPriority ) {
                    if (0 === stripos($encString, $encGroup)) {
                        $currentEncGroup = $encGroup;
                        $currentEncGroupPriority = $encGroupPriority;

                        break;
                    }
                }

                $currentEncGroupPriority = $currentEncGroupPriority ?? INF;

                $encsOrder[ $enc ] = [ $currentEncGroup, $currentEncGroupPriority ];
            }

            usort($encsIndexed,
                static function ($encA, $encB) use (&$encsOrder) {
                    $aPriority = $encsOrder[ $encA ][ 1 ];
                    $bPriority = $encsOrder[ $encB ][ 1 ];

                    return 0
                        ?: ($aPriority <=> $bPriority)
                            ?: (strlen($encB) <=> strlen($encA))
                                ?: strnatcasecmp($encB, $encA);
                }
            );

            $encsByGroup = [];
            foreach ( $encsIndexed as $enc ) {
                $encGroup = $encsOrder[ $enc ][ 0 ];

                $encsByGroup[ $encGroup ][] = $enc;
            }

            $cache[ $cacheKey ] = [ $encsList, $encsByGroup ];
        }

        return $cache[ $cacheKey ];
    }

    /**
     * @param string|string[]|null $encondings
     *
     * @return array<string, string|false>
     */
    public function detect_encoding(string $string, $encondings = '', ?bool $strict = null) : array
    {
        $encondings = $encondings ?? '';
        $strict = $strict ?? true;

        if ('' === $encondings) {
            [ , $encsByGroup ] = $this->list_encodings();

        } elseif (is_array($encondings)) {
            [ , $encsByGroup ] = $this->list_encodings($encondings);

        } elseif (is_string($encondings)) {
            $encsByGroup = [];
            $encsByGroup[ '' ] = array_map('trim', explode(',', $encondings));

        } else {
            throw new LogicException(
                [ 'The `encodings` must be array of suggestions or comma-separated list of encodings', $encondings ]
            );
        }

        $result = [];
        foreach ( $encsByGroup as $encGroup => $encList ) {
            $result[ $encGroup ] = mb_detect_encoding($string, $encList, $strict);
        }

        return $result;
    }

    /**
     * @param string|string[]|null $from_encoding
     *
     * @return array<string, string|false>
     */
    public function convert_encoding($string, string $to_encoding, $from_encoding = '', ?bool $strict = null) : array
    {
        $detectEncodingArray = $this->detect_encoding($string, $from_encoding, $strict);

        $result = [];

        foreach ( $detectEncodingArray as $encGroup => $encoding ) {
            $result[ $encGroup ] = mb_convert_encoding($string, $to_encoding, $encoding);
        }

        return $result;
    }


    /**
     * > делает регистр первой буквы малым
     */
    public function lcfirst(string $string, ?string $mb_encoding = null) : string
    {
        $mbEncodingArgs = [];
        if (null !== $mb_encoding) {
            $mbEncodingArgs[] = $mb_encoding;
        }

        $result = (''
            . mb_strtolower(mb_substr($string, 0, 1, ...$mbEncodingArgs), ...$mbEncodingArgs)
            . mb_substr($string, 1, null, ...$mbEncodingArgs)
        );

        return $result;
    }

    /**
     * > делает регистр первой буквы большим
     */
    public function ucfirst(string $string, ?string $mb_encoding = null) : string
    {
        $mbEncodingArgs = [];
        if (null !== $mb_encoding) {
            $mbEncodingArgs[] = $mb_encoding;
        }

        $result = (''
            . mb_strtoupper(mb_substr($string, 0, 1, ...$mbEncodingArgs), ...$mbEncodingArgs)
            . mb_substr($string, 1, null, ...$mbEncodingArgs)
        );

        return $result;
    }


    /**
     * > пишет каждое слово в предложении с малой буквы
     */
    public function lcwords(string $string, ?string $separators = null, ?string $mb_encoding = null) : string
    {
        $separators = $separators ?? " \t\r\n\f\v";

        $thePreg = Lib::$preg;

        $regex = $thePreg->preg_quote_ord($separators, $mb_encoding);
        $regex = '/(^|[' . $regex . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = $this->lcfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }

    /**
     * > пишет каждое слово в предложении с большой буквы
     */
    public function ucwords(string $string, ?string $separators = null, ?string $mb_encoding = null) : string
    {
        $separators = $separators ?? " \t\r\n\f\v";

        $thePreg = Lib::$preg;

        $regex = $thePreg->preg_quote_ord($separators, $mb_encoding);
        $regex = '/(^|[' . $regex . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = $this->ucfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }


    /**
     * > разбивает слово на группы букв с учетом того, что буква может занимать больше одного байта
     */
    public function str_split(string $string, ?int $length = null, ?string $mb_encoding = null) : array
    {
        $length = $length ?? 1;

        if ($length < 1) {
            throw new LogicException(
                [ 'The `length` should be GT 0', $length ]
            );
        }

        $mbEncodingArgs = [];
        if (null !== $mb_encoding) {
            $mbEncodingArgs[] = $mb_encoding;
        }

        if (PHP_VERSION_ID >= 74000) {
            return mb_str_split($string, $length, ...$mbEncodingArgs);
        }

        $len = mb_strlen($string, ...$mbEncodingArgs);

        $result = [];
        for ( $i = 0; $i < $len; $i += $length ) {
            $result[] = mb_substr($string, $i, $length, ...$mbEncodingArgs);
        }

        return $result;
    }
}
