<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Str\Slugger;

use Gzhegow\Lib\Lib;
use Gzhegow\Support\Traits\Load\StrLoadTrait;
use Gzhegow\Support\Exceptions\RuntimeException;
use Gzhegow\Support\Traits\Load\ItertoolsLoadTrait;
use Gzhegow\Support\Exceptions\Logic\InvalidArgumentException;
use Gzhegow\Support\Exceptions\Runtime\UnexpectedValueException;


class Slugger implements SluggerInterface
{
    const SYMFONY_BINARY_STRING             = 'Symfony\Component\String\BinaryString';
    const SYMFONY_SLUGGER_ASCII_SLUGGER     = 'Symfony\Component\String\Slugger\AsciiSlugger';
    const SYMFONY_SLUGGER_SLUGGER_INTERFACE = 'Symfony\Component\String\Slugger\SluggerInterface';


    /**
     * @var \Symfony\Component\String\Slugger\SluggerInterface $symfonySlugger
     */
    protected $symfonySlugger;

    /**
     * @var string|callable
     */
    protected $localeDefault;

    /**
     * @var array|callable
     */
    protected $sequencesMap = [];
    /**
     * @var array|callable
     */
    protected $symbolsMap = [];
    /**
     * @var array|callable
     */
    protected $ignoreSymbols = [];


    /**
     * @param null|object|\Symfony\Component\String\Slugger\SluggerInterface $symfonySlugger
     *
     * @return object
     */
    public function withSymfonySlugger(?object $symfonySlugger) : object
    {
        if ($symfonySlugger) {
            if (! is_a($symfonySlugger, $interface = static::SYMFONY_SLUGGER_SLUGGER_INTERFACE)) {
                throw new RuntimeException([
                    'Symfony Slugger should implements %s: %s',
                    $interface,
                    $symfonySlugger,
                ]);
            }
        }

        $this->symfonySlugger = $symfonySlugger;

        return $this;
    }


    /**
     * @return \Symfony\Component\String\Slugger\SluggerInterface
     */
    public function newSymfonySlugger() : object
    {
        $commands = [
            'composer require symfony/string',
            'composer require symfony/translation-contracts',
        ];

        if (! class_exists($class = static::SYMFONY_SLUGGER_ASCII_SLUGGER)) {
            throw new RuntimeException([
                'Please, run following: %s',
                $commands,
            ]);
        }

        $defaultLocale = null
            ?? $this->getLocaleDefault()
            ?? $this->getLocaleDefaultFromPhp()
            ?? 'en';

        return new $class($defaultLocale, array_combine(
            $this->getIgnoreSymbols(),
            $this->getIgnoreSymbols()
        ));
    }


    /**
     * @return \Symfony\Component\String\Slugger\SluggerInterface
     */
    public function getSymfonySlugger() : object
    {
        return $this->symfonySlugger = $this->symfonySlugger
            ?? $this->newSymfonySlugger();
    }


    /**
     * @return null|string
     */
    public function getLocaleDefault() : ?string
    {
        $localeDefault = null
            ?? (is_callable($this->localeDefault) ? call_user_func($this->localeDefault) : null)
            ?? (is_string($this->localeDefault) ? $this->localeDefault : null)
            ?? null;

        return $localeDefault;
    }

    /**
     * @return null|string
     */
    public function getLocaleDefaultFromPhp() : ?string
    {
        $localeDefault = null;

        if (extension_loaded('intl') && function_exists($func = 'locale_get_default')) {
            $localeDefault = $func();

        } elseif ('C' !== ($locale = setlocale(LC_ALL, 0))) {
            $localeDefault = $locale;
        }

        return $localeDefault;
    }


    /**
     * @return array
     */
    public function getSequencesMapNative() : array
    {
        $sequencesMap = $this->fetchSequenceMapNative();

        $sequences = $this->prepareSequencesMap($sequencesMap);

        return $sequences;
    }

    /**
     * @return array
     */
    public function getSequencesMap() : array
    {
        $sequencesMap = null
            ?? (is_callable($this->sequencesMap) ? call_user_func($this->sequencesMap) : null)
            ?? (is_array($this->sequencesMap) ? $this->sequencesMap : null)
            ?? [];

        $sequences = $this->prepareSequencesMap($sequencesMap);

        return $sequences;
    }


    /**
     * @return array
     */
    public function getSymbolsMapNative() : array
    {
        $symbolsMap = $this->fetchSymbolsMapNative();

        $symbols = $this->prepareSymbolsMap($symbolsMap);

        return $symbols;
    }

    /**
     * @return array
     */
    public function getSymbolsMap() : array
    {
        $symbolsMap = null
            ?? (is_callable($this->symbolsMap) ? call_user_func($this->symbolsMap) : null)
            ?? (is_array($this->symbolsMap) ? $this->symbolsMap : null)
            ?? [];

        $symbols = $this->prepareSymbolsMap($symbolsMap);

        return $symbols;
    }


    /**
     * @return array
     */
    public function getIgnoreSymbols() : array
    {
        $ignoreSymbols = null
            ?? (is_callable($this->ignoreSymbols) ? call_user_func($this->ignoreSymbols) : null)
            ?? (is_array($this->ignoreSymbols) ? $this->ignoreSymbols : null)
            ?? [];

        $ignore = $this->prepareIgnoreSymbols($ignoreSymbols);

        return $ignore;
    }


    /**
     * @param array|\Closure $localeDefault
     *
     * @return static
     */
    public function localeDefault($localeDefault)
    {
        if ($localeDefault) {
            if (! (is_string($localeDefault) || $localeDefault instanceof \Closure)) {
                throw new InvalidArgumentException([
                    'The `localeDefault` should be string or \Closure: %s',
                    $localeDefault,
                ]);
            }
        }

        $this->localeDefault = $localeDefault;

        return $this;
    }


    /**
     * @param array|\Closure $sequencesMap
     *
     * @return static
     */
    public function sequencesMap($sequencesMap)
    {
        if ($sequencesMap) {
            if (! (is_array($sequencesMap) || $sequencesMap instanceof \Closure)) {
                throw new InvalidArgumentException([
                    'The `sequencesMap` should be array or \Closure: %s',
                    $sequencesMap,
                ]);
            }
        }

        $this->sequencesMap = $sequencesMap ?? [];

        return $this;
    }

    /**
     * @param array|\Closure $symbolsMap
     *
     * @return static
     */
    public function symbolsMap($symbolsMap)
    {
        if ($symbolsMap) {
            if (! (is_array($symbolsMap) || $symbolsMap instanceof \Closure)) {
                throw new InvalidArgumentException([
                    'The `symbolsMap` should be array or \Closure: %s',
                    $symbolsMap,
                ]);
            }
        }

        $this->symbolsMap = $symbolsMap ?? [];

        return $this;
    }

    /**
     * @param array|\Closure $ignoreSymbols
     *
     * @return static
     */
    public function ignoreSymbols($ignoreSymbols)
    {
        if ($ignoreSymbols) {
            if (! (is_array($ignoreSymbols) || $ignoreSymbols instanceof \Closure)) {
                throw new InvalidArgumentException([
                    'The `ignoreSymbols` should be array or \Closure: %s',
                    $ignoreSymbols,
                ]);
            }
        }

        $this->ignoreSymbols = $ignoreSymbols ?? [];

        return $this;
    }


    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return null|string
     */
    public function slug(string $string, string $delimiter = null, string $locale = null) : ?string
    {
        $translitTransliterator = null;
        $translitSymfonySlugger = null;
        $translitNative = null;

        $result = null
            ?? ($translitTransliterator = $this->translitTransliterator($string, $delimiter, $locale))
            ?? ($translitSymfonySlugger = $this->translitSymfonySlugger($string, $delimiter, $locale))
            ?? ($translitNative = $this->translitNative($string, $delimiter, $locale)) //
        ;

        return $result;
    }


    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return null|string
     */
    protected function translitTransliterator(string $string, string $delimiter = null, string $locale = null) : ?string
    {
        if ('' === $string) return '';

        if (! (extension_loaded('intl') && function_exists($func = 'transliterator_transliterate'))) {
            return null;
        }

        $delimiter = $delimiter ?? '-';

        $result = $string;

        $result = $this->transliterateTransliterator($result, $delimiter, $locale);
        $result = $this->transliterateUser($result, $delimiter, $locale);
        $result = $this->transliterateDelimiter($result, $delimiter, $locale);

        return $result;
    }

    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return null|string
     */
    protected function translitSymfonySlugger(string $string, string $delimiter = null, string $locale = null) : ?string
    {
        if ('' === $string) return '';

        if (! interface_exists($interface = static::SYMFONY_SLUGGER_SLUGGER_INTERFACE)) {
            return null;
        }

        if (! class_exists($class = static::SYMFONY_BINARY_STRING)) {
            return null;
        }

        // @gzhegow > symfony transliterator fails if `intl` is not exists and string is in UTF encoding
        $isUTF = (new $class($string))->{$method = 'isUtf8'}();
        if ($isUTF && ! (extension_loaded('intl') && function_exists($func = 'transliterator_transliterate'))) {
            return null;
        }

        $delimiter = $delimiter ?? '-';

        $result = $this->getSymfonySlugger()->slug($string, $delimiter, $locale)->toString();

        return $result;
    }

    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return string
     */
    protected function translitNative(string $string, string $delimiter = null, string $locale = null) : string
    {
        if ('' === $string) return '';

        $theStr = Lib::str();

        $delimiter = $delimiter ?? '-';

        $result = $string;

        $before = $theStr->mb_mode_static(true);

        $result = $this->transliterateNative($result, $delimiter, $locale);
        $result = $this->transliterateUser($result, $delimiter, $locale);
        $result = $this->transliterateDelimiter($result, $delimiter, $locale);

        $theStr->mb_mode_static($before);

        return $result;
    }


    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return string
     */
    protected function transliterateTransliterator(string $string, string $delimiter = null, string $locale = null) : string
    {
        $join = [];

        // split unicode accents and symbols, e.g. "Å" > "A°"
        $join[] = 'NFKD';

        // convert everything to the Latin charset e.g. "ま" > "ma":
        $join[] = 'Latin';

        // convert to ASCII
        $join[] = 'Latin/US-ASCII';

        // cache, remove non-printables, restore
        $join[] = 'NFD';
        $join[] = '[:Nonspacing Mark:] Remove';
        $join[] = 'NFC';

        $join = implode('; ', $join);

        $func = 'transliterator_transliterate';
        $result = $func($join, $string);

        return $result;
    }

    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return string
     */
    protected function transliterateNative(string $string, string $delimiter = null, string $locale = null) : string
    {
        $result = $string;

        $sequncesMap = $this->getSequencesMapNative();
        $result = str_replace(
            array_keys($sequncesMap),
            array_values($sequncesMap),
            $result
        );

        $symbolsMap = $this->getSymbolsMapNative();
        foreach ( $symbolsMap as $replacement => $search ) {
            $result = str_replace($search, $replacement, $result);
        }

        return $result;
    }


    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return string
     */
    protected function transliterateUser(string $string, string $delimiter = null, string $locale = null) : string
    {
        $result = $string;

        $sequencesMap = $this->getSequencesMap();
        $result = str_replace(
            array_keys($sequencesMap),
            array_values($sequencesMap),
            $result
        );

        $symbolsMap = $this->getSymbolsMap();
        foreach ( $symbolsMap as $replacement => $search ) {
            $result = str_replace($search, $replacement, $result);
        }

        return $result;
    }

    /**
     * @param string      $string
     * @param null|string $delimiter
     * @param null|string $locale
     *
     * @return string
     */
    protected function transliterateDelimiter(string $string, string $delimiter = null, string $locale = null) : string
    {
        $result = $string;

        $replacer = "\0";

        $result = preg_replace('~' . preg_quote($delimiter, '/') . '~u', $replacer, $result);

        $ignoreSymbols = $this->getIgnoreSymbols();
        $ignoreSymbols = preg_quote(implode('', $ignoreSymbols), '/');

        $result = preg_replace('~[^\p{L}\d' . $ignoreSymbols . ']+~u', $replacer, $result);

        $result = trim($result, $replacer);

        $result = str_replace($replacer, $delimiter, $result);

        return $result;
    }


    /**
     * @param array $sequencesMap
     *
     * @return array
     */
    protected function prepareSequencesMap(array $sequencesMap) : array
    {
        $theItertools = Lib::itertools();
        $theStr = Lib::str();

        $fnStrToUpper = $theStr->mb_func('strtoupper');

        $sequences = [];

        foreach ( $sequencesMap as $sequence ) {
            $keys = array_keys($sequence);
            $sequence = array_values($sequence);

            $keysCase = [];
            foreach ( $keys as $idx => $letter ) {
                $keysCase[ $idx ][] = $letter;
                $keysCase[ $idx ][] = $fnStrToUpper($letter);
            }

            $sequenceCase = [];
            foreach ( $sequence as $idx => $letter ) {
                $sequenceCase[ $idx ][] = $letter;
                $sequenceCase[ $idx ][] = $fnStrToUpper($letter);
            }

            $keysCase = iterator_to_array($theItertools->product_it(...$keysCase));
            $sequenceCase = iterator_to_array($theItertools->product_it(...$sequenceCase));

            foreach ( array_keys($keysCase) as $idx ) {
                $search = implode('', $keysCase[ $idx ]);
                $replacement = implode('', $sequenceCase[ $idx ]);

                if (($search !== $replacement) && ! isset($sequences[ $replacement ])) {
                    $sequences[ $search ] = $replacement;
                }
            }
        }

        return $sequences;
    }

    /**
     * @param array $symbolsMap
     *
     * @return array
     */
    protected function prepareSymbolsMap(array $symbolsMap)
    {
        $theStr = Lib::str();

        $fnStrSplit = $theStr->mb_func('str_split');
        $fnStrlen = $theStr->mb_func('strlen');
        $fnStrtolower = $theStr->mb_func('strtolower');
        $fnStrtoupper = $theStr->mb_func('strtoupper');

        $symbols = [];

        foreach ( $symbolsMap as $a => $b ) {
            $aLower = $fnStrtolower($a);
            $aUpper = $fnStrtoupper($a);

            $b = is_array($b) ? $b : [ $b ];

            $list = [];
            foreach ( $b as $bb ) {
                $list = array_merge($list, $fnStrSplit($bb));
            }

            foreach ( $list as $bb ) {
                $bbLen = $fnStrlen($bb);
                $bbLower = $fnStrtolower($bb);
                $bbUpper = $fnStrtoupper($bb);

                // incorrect: ß -> 'SS'
                if (false
                    || ($bbLen !== $fnStrlen($bbLower))
                    || ($bbLen !== $fnStrlen($bbUpper))
                ) {
                    throw new UnexpectedValueException([
                        'Case change cause unexpected lenght difference, you should move pair into sequenceMap: %s / %s',
                        [ $a => $bb ],
                        [ $bb, $bbLower, $bbUpper ],
                    ]);
                }

                if (! isset($symbols[ $bbLower ])) {
                    $symbols[ $aLower ][] = $bbLower;
                }

                if (! isset($symbols[ $bbUpper ])) {
                    $symbols[ $aUpper ][] = $bbUpper;
                }
            }
        }

        return $symbols;
    }

    /**
     * @param string|string[] $ignoreSymbols
     *
     * @return array
     */
    protected function prepareIgnoreSymbols($ignoreSymbols) : array
    {
        $ignoreSymbols = is_iterable($ignoreSymbols)
            ? $ignoreSymbols
            : ($ignoreSymbols ? [ $ignoreSymbols ] : []);

        $theStr = Lib::str();

        $fnStrSplit = $theStr->mb_func('str_split');

        $ignore = [];

        foreach ( $ignoreSymbols as $symbol ) {
            foreach ( $fnStrSplit($symbol) as $sym ) {
                $ignore[ $sym ] = true;
            }
        }

        return array_keys($ignore);
    }


    /**
     * @return string[]
     */
    protected function fetchSequenceMapNative() : array
    {
        return [
            'ẚ' => [ 'ẚ' => 'a' ], // [0] => ẚ [1] => ẚ [2] => Aʾ
            'ß' => [ 'ß' => 'ss' ], // [0] => ß [1] => ß [2] => SS

            'ый' => [ 'ы' => 'i', 'й' => 'y' ],
            'ех' => [ 'е' => 'c', 'х' => 'kh' ],
            'сх' => [ 'с' => 'c', 'х' => 'kh' ],
            'цх' => [ 'ц' => 'c', 'х' => 'kh' ],
        ];
    }

    /**
     * @return string[]
     */
    protected function fetchSymbolsMapNative() : array
    {
        return [
            ' ' => 'ъь',

            'a' => [ 'aàáâãäåæāăąǎǟǡǣǻǽȁȧаḁạảấầẩẫậắằẳẵặ' ],
            'b' => [ 'þб' ],
            'c' => [ 'çćĉċčц' ],
            'd' => [ 'ðďд' ],
            'e' => [ 'eèéêẽēĕėëẻěȅȇẹȩęḙḛềếễểḕḗệḝеёє' ],
            'f' => [ 'ƒф' ],
            'g' => [ 'ĝğġģг' ],
            'h' => [ 'ĥħх' ],
            'i' => [ 'iìíîĩīĭïỉǐịįȉȋḭḯиыії' ],
            'j' => [ 'ĵй' ],
            'k' => [ 'ķĸĺļľŀłк' ],
            'l' => [ 'ĺļľŀłл' ],
            'm' => [ 'м' ],
            'n' => [ 'ñńņňʼnŋн' ],
            'o' => [ 'oòóôõōŏȯöỏőǒȍȏơǫọøồốỗổȱȫȭṍṏṑṓờớỡởợǭộǿœо' ],
            'p' => [ 'ƥп' ],
            'r' => [ 'ŕŗřр' ],
            's' => [ 'śŝşšșс' ],
            't' => [ 'ţťŧțт' ],
            'u' => [ 'uùúûũūŭüủůűǔȕȗưụṳųṷṵṹṻǖǜǘǖǚừứữửựуюў' ],
            'v' => [ 'в' ],
            'w' => [ 'ŵ' ],
            'y' => [ 'ýÿŷы' ],
            'z' => [ 'źżžз' ],

            'ch'   => [ 'ч' ],
            'dj'   => [ 'đ' ],
            'eh'   => [ 'э' ],
            'ij'   => [ 'ĳ' ],
            'oe'   => [ 'œ' ],
            'sh'   => [ 'ш' ],
            'shch' => [ 'щ' ],
            'ss'   => [ 'ſ' ],
            'ue'   => [ 'ü' ],
            'ya'   => [ 'я' ],
            'yo'   => [ 'ё' ],
            'yu'   => [ 'ю' ],
            'zh'   => [ 'ж' ],
        ];
    }
}
