<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Str\Slugger;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class Slugger implements SluggerInterface
{
    const SYMFONY_SLUGGER_SLUGGER_INTERFACE = 'Symfony\Component\String\Slugger\SluggerInterface';


    /**
     * @var \Symfony\Component\String\Slugger\SluggerInterface
     */
    protected $symfonySlugger;

    /**
     * @var array<string, bool>
     */
    protected $presets = [];

    /**
     * @var string|callable|null
     */
    protected $localeDefault;

    /**
     * @var array<string, callable>
     */
    protected $fnIgnoreSymbolMapByPreset = [
        'php-native' => [ self::class, 'ignoreSymbolMapPhpNative' ],
    ];
    /**
     * @var array<string, callable>
     */
    protected $fnSequenceMapByPreset = [
        'php-native' => [ self::class, 'sequenceMapPhpNative' ],
    ];
    /**
     * @var array<string, callable>
     */
    protected $fnSymbolMapByPreset = [
        'php-native' => [ self::class, 'symbolMapPhpNative' ],
    ];

    /**
     * @var array<string, bool>
     */
    protected $ignoreSymbolMapCurrent;
    /**
     * @var array<string, string>
     */
    protected $sequenceMapCurrent;
    /**
     * @var array<string, string>
     */
    protected $symbolMapCurrent;
    /**
     * @var array<string, bool>
     */
    protected $knownSymbolMapCurrent;


    /**
     * @param array<string, array{ 0: callable, 1: callable, 2: callable }> $presets
     */
    public function __construct(array $presets = [])
    {
        if (0 !== count($presets)) {
            $this->fnIgnoreSymbolMapByPreset = [];
            $this->fnSequenceMapByPreset = [];
            $this->fnSymbolMapByPreset = [];
        }

        foreach ( $presets as $preset => $callables ) {
            if ((! is_string($preset)) || ('' === $preset)) {
                throw new LogicException(
                    'The `preset` should be non-empty string'
                );
            }

            $fnIgnoreSymbolMap = $callables[ 'fnIgnoreSymbolMap' ] ?? $callables[ 2 ] ?? null;
            $fnSequenceMap = $callables[ 'fnSequenceMap' ] ?? $callables[ 0 ] ?? null;
            $fnSymbolMap = $callables[ 'fnSymbolMap' ] ?? $callables[ 1 ] ?? null;

            if (null !== $fnIgnoreSymbolMap) {
                $this->fnIgnoreSymbolMapByPreset[ $preset ] = $fnIgnoreSymbolMap;
            }

            if (null !== $fnSequenceMap) {
                $this->fnSequenceMapByPreset[ $preset ] = $fnSequenceMap;
            }

            if (null !== $fnSymbolMap) {
                $this->fnSymbolMapByPreset[ $preset ] = $fnSymbolMap;
            }
        }
    }


    /**
     * @param null|object|\Symfony\Component\String\Slugger\SluggerInterface $symfonySlugger
     *
     * @return object
     */
    public function withSymfonySlugger(?object $symfonySlugger) : object
    {
        if (null !== $symfonySlugger) {
            if (! is_a($symfonySlugger, $interface = static::SYMFONY_SLUGGER_SLUGGER_INTERFACE)) {
                throw new RuntimeException(
                    [
                        'The `symfonySlugger` should be instance of: ' . $interface,
                        $interface,
                        $symfonySlugger,
                    ]
                );
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

        $asciiSluggerClass = '\Symfony\Component\String\Slugger\AsciiSlugger';
        if (! class_exists($asciiSluggerClass)) {
            throw new RuntimeException(
                [ 'Please, run following: ' . implode(' | ', $commands) ]
            );
        }

        $localeDefault = null
            ?? $this->getLocaleDefault()
            ?? $this->getLocaleDefaultPhp()
            ?? 'en';

        [
            $ignoreSymbolMap,
        ] = $this->getSymbolMapsCurrent();

        $ignoreSymbolMapSymfony = [];
        if (0 !== count($ignoreSymbolMap)) {
            $ignoreSymbolMapSymfony = array_keys($ignoreSymbolMap);
            $ignoreSymbolMapSymfony = array_combine(
                $ignoreSymbolMapSymfony,
                $ignoreSymbolMapSymfony
            );
        }

        return new $asciiSluggerClass(
            $localeDefault,
            $ignoreSymbolMapSymfony
        );
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
     * @return static
     */
    public function withPresets($presets)
    {
        $presets = is_array($presets)
            ? $presets
            : ($presets ? [ $presets ] : []);

        if (0 === count($presets)) {
            return $this;
        }

        $_presets = [];
        foreach ( $presets as $i => $preset ) {
            $_preset = (string) $preset;

            if ('' === $_preset) {
                throw new LogicException(
                    'Each of `presets` should be non-empty string'
                );
            }

            $_presets[ $preset ] = true;
        }

        if ($this->presets !== $_presets) {
            $this->presets = $_presets;

            $this->ignoreSymbolMapCurrent = null;
            $this->sequenceMapCurrent = null;
            $this->symbolMapCurrent = null;
            $this->knownSymbolMapCurrent = null;
        }

        return $this;
    }


    public function getSymbolMapsCurrent() : array
    {
        if (! $this->presets) {
            return [
                [],
                [],
                [],
                [],
            ];
        }

        if (null === $this->knownSymbolMapCurrent) {
            $presetsData = $this->prepareSymbolMaps($this->presets);

            [
                $this->ignoreSymbolMapCurrent,
                $this->sequenceMapCurrent,
                $this->symbolMapCurrent,
                $this->knownSymbolMapCurrent,
            ] = $presetsData;
        }

        return [
            $this->ignoreSymbolMapCurrent,
            $this->sequenceMapCurrent,
            $this->symbolMapCurrent,
            $this->knownSymbolMapCurrent,
        ];
    }

    protected function prepareSymbolMaps(array $presets) : array
    {
        $ignoreSymbolMap = [];
        $sequenceMap = [];
        $symbolMap = [];
        $knownSymbolMap = [];

        foreach ( $presets as $preset => $bool ) {
            $fnIgnoreSymbolMapCurrent = $this->fnIgnoreSymbolMapByPreset[ $preset ] ?? null;

            if (null !== $fnIgnoreSymbolMapCurrent) {
                $array = call_user_func_array($fnIgnoreSymbolMapCurrent, []);
                $array = $this->prepareIgnoreSymbolMap(
                    $array,
                    $knownSymbolMapCurrent
                );

                $ignoreSymbolMap += $array;
                $knownSymbolMap += $knownSymbolMapCurrent;
            }
        }

        foreach ( $presets as $preset => $bool ) {
            $fnSequenceMapCurrent = $this->fnSequenceMapByPreset[ $preset ] ?? null;
            $fnSymbolMapCurrent = $this->fnSymbolMapByPreset[ $preset ] ?? null;

            if (null !== $fnSequenceMapCurrent) {
                $array = call_user_func_array($fnSequenceMapCurrent, []);
                $array = $this->prepareSequenceMap(
                    $array, $ignoreSymbolMap,
                    $knownSymbolMapCurrent
                );

                $sequenceMap += $array;
                $knownSymbolMap += $knownSymbolMapCurrent;
            }

            if (null !== $fnSymbolMapCurrent) {
                $array = call_user_func_array($fnSymbolMapCurrent, []);
                $array = $this->prepareSymbolMap(
                    $array, $ignoreSymbolMap,
                    $knownSymbolMapCurrent
                );

                $symbolMap += $array;
                $knownSymbolMap += $knownSymbolMapCurrent;
            }
        }

        if ($knownSymbolMap) {
            ksort($knownSymbolMap);
        }

        return [
            $ignoreSymbolMap,
            $sequenceMap,
            $symbolMap,
            $knownSymbolMap,
        ];
    }


    /**
     * @return null|string
     */
    public function getLocaleDefault() : ?string
    {
        $localeDefault = null
            ?? $this->getLocaleDefaultUser()
            ?? $this->getLocaleDefaultPhp();

        return $localeDefault;
    }

    /**
     * @return null|string
     */
    public function getLocaleDefaultUser() : ?string
    {
        $localeDefaultUser = null
            ?? (is_callable($this->localeDefault) ? call_user_func($this->localeDefault) : null)
            ?? (is_string($this->localeDefault) ? $this->localeDefault : null)
            ?? null;

        return $localeDefaultUser;
    }

    /**
     * @return null|string
     */
    public function getLocaleDefaultPhp() : ?string
    {
        $localeDefaultPhp = null;

        if (true
            && extension_loaded('intl')
            && function_exists($func = 'locale_get_default')
        ) {
            $localeDefaultPhp = $func();

        } elseif ('C' !== ($locale = setlocale(LC_ALL, 0))) {
            $localeDefaultPhp = $locale;
        }

        return $localeDefaultPhp;
    }

    /**
     * @param string|callable|null $localeDefault
     *
     * @return static
     */
    public function localeDefault($localeDefault)
    {
        if (null !== $localeDefault) {
            if (! (false
                || is_string($localeDefault)
                || is_callable($localeDefault)
            )) {
                throw new LogicException(
                    [
                        'The `localeDefault` should be string or callable',
                        $localeDefault,
                    ]
                );
            }
        }

        $this->localeDefault = $localeDefault;

        return $this;
    }


    public function translit(string $string, ?string $delimiter = null, ?string $locale = null) : ?string
    {
        $_delimiter = $delimiter ?? '-';

        if (mb_strlen($_delimiter) > 1) {
            throw new LogicException(
                'The `delimiter` should be exactly one letter'
            );
        }

        if ('' === $string) {
            return '';
        }

        $result = null
            ?? $this->translitSymfonySlugger($string, $_delimiter, $locale)
            ?? $this->translitPresets($string, $_delimiter, $locale)
            ?? $this->translitTransliterator($string, $_delimiter, $locale)
            ?? $this->translitPhpNative($string, $_delimiter, $locale);

        return $result;
    }

    public function slug(string $string, ?string $delimiter = null, ?string $locale = null) : ?string
    {
        $_delimiter = $delimiter ?? '-';

        if (mb_strlen($_delimiter) > 1) {
            throw new LogicException(
                'The `delimiter` should be exactly one letter'
            );
        }

        $result = $this->translit($string, $delimiter, $locale);

        $regex = sprintf('\x{%X}', mb_ord($_delimiter));
        $regex = '/[' . $regex . ' ]+/iu';
        $result = preg_replace($regex, $_delimiter, $result);

        $result = trim($result, $_delimiter);

        return $result;
    }


    protected function translitSymfonySlugger(string $string, ?string $delimiter = null, ?string $locale = null) : ?string
    {
        if (! interface_exists($interface = static::SYMFONY_SLUGGER_SLUGGER_INTERFACE)) {
            return null;
        }

        $class = '\Symfony\Component\String\BinaryString';
        if (! class_exists($class)) {
            return null;
        }

        if ('' === $string) {
            return '';
        }

        // @gzhegow > symfony transliterator fails if `intl` is not exists and string is in UTF-8 encoding
        $isUtf8 = (new $class($string))->{$method = 'isUtf8'}();
        if (true
            && $isUtf8
            && ! (true
                && extension_loaded('intl')
                && function_exists('transliterator_transliterate')
            )
        ) {
            return null;
        }

        $_delimiter = $delimiter ?? '-';
        $_locale = $locale ?? $this->getLocaleDefault();

        $theSymfonySlugger = $this->getSymfonySlugger();

        $result = $theSymfonySlugger->slug($string, $_delimiter, $_locale);

        $result = $result->toString();

        return $result;
    }

    protected function translitPresets(string $string, ?string $delimiter = null, ?string $locale = null) : ?string
    {
        if (! $this->presets) {
            return null;
        }

        if ('' === $string) {
            return '';
        }

        $thePreg = Lib::preg();

        $_delimiter = $delimiter ?? '-';
        $_locale = $locale ?? $this->getLocaleDefault();

        $result = $string;

        [
            $ignoreSymbolMap,
            $sequnceMap,
            $symbolMap,
            $knownSymbolMap,
        ] = $this->getSymbolMapsCurrent();

        $result = str_replace(
            array_keys($sequnceMap),
            array_values($sequnceMap),
            $result
        );

        $result = str_replace(
            array_keys($symbolMap),
            array_values($symbolMap),
            $result
        );

        $knownSymbolMapRegex = array_keys($knownSymbolMap);
        $knownSymbolMapRegex = implode('', $knownSymbolMapRegex);
        $knownSymbolMapRegex = '/[^' . $thePreg->preg_quote_ord($knownSymbolMapRegex) . '0-9 ]/iu';

        $result = preg_replace($knownSymbolMapRegex, $_delimiter, $result);

        return $result;
    }

    protected function translitTransliterator(string $string, ?string $delimiter = null, ?string $locale = null) : ?string
    {
        if (! (true
            && extension_loaded('intl')
            && function_exists('transliterator_transliterate')
        )) {
            return null;
        }

        if ('' === $string) {
            return '';
        }

        $_delimiter = $delimiter ?? '-';
        $_locale = $locale ?? $this->getLocaleDefault();

        $result = $string;

        $rules = [];

        // > split unicode accents and symbols, e.g. "Å" > "A°"
        $rules[] = 'NFKD';

        // > convert everything to the Latin charset e.g. "ま" > "ma":
        $rules[] = 'Latin';

        // > convert to ASCII
        $rules[] = 'Latin/US-ASCII';

        // > cache, remove non-printables, restore
        $rules[] = 'NFD';
        $rules[] = '[:Nonspacing Mark:] Remove';
        $rules[] = 'NFC';

        $rules = implode('; ', $rules);

        $result = transliterator_transliterate(
            $rules,
            $string
        );

        $knownSymbolMapRegex = '/[^a-z0-9 ]/iu';
        $result = preg_replace($knownSymbolMapRegex, $_delimiter, $result);

        return $result;
    }

    protected function translitPhpNative(string $string, ?string $delimiter = null, ?string $locale = null) : string
    {
        if ('' === $string) {
            return '';
        }

        $theStr = Lib::str();

        $_delimiter = $delimiter ?? '-';
        $_locale = $locale ?? $this->getLocaleDefault();

        $result = $string;

        $this->withPresets('php-native');

        [
            $ignoreSymbolMap,
            $sequnceMap,
            $symbolMap,
            $knownSymbolMap,
        ] = $this->getSymbolMapsCurrent();

        $result = str_replace(
            array_keys($sequnceMap),
            array_values($sequnceMap),
            $result
        );

        foreach ( $symbolMap as $replacement => $search ) {
            $result = str_replace($search, $replacement, $result);
        }

        $knownSymbolMapRegex = '/[^a-z0-9 ]/iu';
        $result = preg_replace($knownSymbolMapRegex, $_delimiter, $result);

        return $result;
    }


    /**
     * @param array<string, bool> $knownSymbolMap
     *
     * @return array<string, string>
     */
    protected function prepareSequenceMap(
        array $sequenceMap, array $ignoreSymbolMap,
        ?array &$knownSymbolMap = null
    ) : array
    {
        $knownSymbolMap = [];

        $theItertools = Lib::itertools();
        $theMb = Lib::mb();

        $result = [];

        // > example
        // [
        //     'ẚ' => [ 'ẚ' => 'a' ],
        //     'ß' => [ 'ß' => 'ss' ],
        //     'ый' => [ 'ы' => 'i', 'й' => 'y' ],
        // ]

        foreach ( $sequenceMap as $sequence ) {
            $aList = array_keys($sequence);
            $bList = array_values($sequence);

            $aCase = [];
            foreach ( $aList as $i => $a ) {
                if (! is_string($a) || (mb_strlen($a) !== 1)) {
                    throw new LogicException(
                        'Each of `lettersIn` must be letter'
                    );
                }

                $aLower = mb_strtolower($a);
                $aUpper = mb_strtoupper($a);

                if (false
                    || isset($ignoreSymbolMap[ $aLower ])
                    || isset($ignoreSymbolMap[ $aUpper ])
                ) {
                    throw new RuntimeException(
                        [
                            'Letter has conflict with `ignoreSymbolMap`',
                            $aLower,
                            $aUpper,
                            $ignoreSymbolMap,
                        ]
                    );
                }

                $aCase[ $i ][] = $aLower;
                $aCase[ $i ][] = $aUpper;
            }

            $bCase = [];
            foreach ( $bList as $i => $b ) {
                $bCase[ $i ][] = mb_strtolower($b);
                $bCase[ $i ][] = mb_strtoupper($b);
            }

            $aGen = $theItertools->product_it(...$aCase);
            $bGen = $theItertools->product_it(...$bCase);

            $aProductArray = iterator_to_array($aGen);
            $bProductArray = iterator_to_array($bGen);

            foreach ( array_keys($aProductArray) as $i ) {
                $search = implode('', $aProductArray[ $i ]);
                $replacement = implode('', $bProductArray[ $i ]);

                if (isset($result[ $search ])) {
                    throw new LogicException(
                        [
                            'Unable to add sequence due to search string is already registered',
                            $search,
                            $result,
                        ]
                    );
                }

                $result[ $search ] = $replacement;

                $array = $theMb->str_split($replacement, 1);
                foreach ( $array as $l ) {
                    $knownSymbolMap[ $l ] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool> $knownSymbolMap
     *
     * @return array<string, string>
     */
    protected function prepareSymbolMap(
        array $symbolMap, array $ignoreSymbolMap,
        ?array &$knownSymbolMap = null
    ) : array
    {
        $knownSymbolMap = [];

        $theMb = Lib::mb();

        $result = [];

        foreach ( $symbolMap as $a => $b ) {
            $aLower = mb_strtolower($a);
            $aUpper = mb_strtoupper($a);

            if (false
                || isset($ignoreSymbolMap[ $aLower ])
                || isset($ignoreSymbolMap[ $aUpper ])
            ) {
                throw new RuntimeException(
                    [
                        'Letter has conflict with `ignoreSymbolMap`',
                        $aLower,
                        $aUpper,
                        $ignoreSymbolMap,
                    ]
                );
            }

            $bArray = null
                ?? (is_array($b) ? $b : null)
                ?? (is_string($b) ? [ $b ] : null)
                ?? [];

            if (0 === count($bArray)) {
                throw new LogicException(
                    'The `bArray` should be non-empty array'
                );
            }

            $list = [];
            foreach ( $bArray as $i => $v ) {
                if (is_string($i)) {
                    $v = $i;
                }

                $split = $theMb->str_split($v, 1);

                $list = array_merge($list, $split);
            }

            foreach ( $list as $bb ) {
                $bbLower = mb_strtolower($bb);
                $bbUpper = mb_strtoupper($bb);

                $bbSize = strlen($bb);
                $bbLowerSize = strlen($bbLower);
                $bbUpperSize = strlen($bbUpper);

                $bbLen = mb_strlen($bb);
                $bbLowerLen = mb_strlen($bbLower);
                $bbUpperLen = mb_strlen($bbLower);

                // > example size/length difference when change case: `ß` -> `SS`
                if (false
                    || ($bbSize !== $bbLowerSize)
                    || ($bbSize !== $bbUpperSize)
                    || ($bbLen !== $bbLowerLen)
                    || ($bbLen !== $bbUpperLen)
                ) {
                    throw new LogicException(
                        [
                            'Changing case forces unexpected size/length difference, you should move this symbol to `sequenceMap`',
                            [ $a => $bb ],
                            [ $bb, $bbLower, $bbUpper ],
                        ]
                    );
                }

                if (false
                    || isset($result[ $bbLower ])
                    || isset($result[ $bbUpper ])
                ) {
                    throw new LogicException(
                        [
                            'Unable to add letter to results of `symbolMap` due to letter is known as source',
                        ]
                    );
                }

                $result[ $bbLower ] = $aLower;
                $result[ $bbUpper ] = $aUpper;

                $array = $theMb->str_split($aLower, 1);
                foreach ( $array as $l ) {
                    $knownSymbolMap[ $l ] = true;
                }

                $array = $theMb->str_split($aUpper, 1);
                foreach ( $array as $l ) {
                    $knownSymbolMap[ $l ] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool> $knownSymbolMap
     *
     * @return array<string, bool>
     */
    protected function prepareIgnoreSymbolMap(array $ignoreSymbols, ?array &$knownSymbolMap = null) : array
    {
        $knownSymbolMap = [];

        $theMb = Lib::mb();

        $result = [];

        foreach ( $ignoreSymbols as $i => $letter ) {
            if (is_string($i)) {
                $letter = $i;
            }

            $letterArray = null
                ?? (is_array($letter) ? $letter : null)
                ?? (is_string($letter) ? [ $letter ] : null)
                ?? [];

            $letters = $theMb->str_split($letter, 1);

            foreach ( $letters as $l ) {
                if (! isset($result[ $l ])) {
                    $result[ $l ] = true;

                    $knownSymbolMap[ $l ] = true;
                }
            }
        }

        return $result;
    }


    private static function ignoreSymbolMapPhpNative() : array
    {
        return [
            // 'a' => true,
        ];
    }

    private static function sequenceMapPhpNative() : array
    {
        return [
            'ый' => [ 'ы' => 'i', 'й' => 'y' ],
            'ех' => [ 'е' => 'c', 'х' => 'kh' ],
            'сх' => [ 'с' => 'c', 'х' => 'kh' ],
            'цх' => [ 'ц' => 'c', 'х' => 'kh' ],
            //
            'ẚ'  => [ 'ẚ' => 'a' ], // > ẚ -> [lower]: ẚ -> [upper]: Aʾ
            'ß'  => [ 'ß' => 'ss' ], // > ß -> [lower]: ß -> [upper]: SS
            'ſ'  => [ 'ſ' => 's' ], // > ſ -> [lower]: ſ -> [upper]: S
        ];
    }

    private static function symbolMapPhpNative() : array
    {
        return [
            '' => [
                'ъ' => true,
                'ь' => true,
            ],

            'a' => [
                'a' => true,
                'à' => true,
                'á' => true,
                'â' => true,
                'ã' => true,
                'ä' => true,
                'å' => true,
                'æ' => true,
                'ā' => true,
                'ă' => true,
                'ą' => true,
                'ǎ' => true,
                'ǟ' => true,
                'ǡ' => true,
                'ǣ' => true,
                'ǻ' => true,
                'ǽ' => true,
                'ȁ' => true,
                'ȧ' => true,
                'а' => true,
                'ḁ' => true,
                'ạ' => true,
                'ả' => true,
                'ấ' => true,
                'ầ' => true,
                'ẩ' => true,
                'ẫ' => true,
                'ậ' => true,
                'ắ' => true,
                'ằ' => true,
                'ẳ' => true,
                'ẵ' => true,
                'ặ' => true,
            ],
            'b' => [
                'þ' => true,
                'б' => true,
            ],
            'c' => [
                'ç' => true,
                'ć' => true,
                'ĉ' => true,
                'ċ' => true,
                'č' => true,
                'ц' => true,
            ],
            'd' => [
                'ð' => true,
                'ď' => true,
                'д' => true,
            ],
            'e' => [
                'e' => true,
                'è' => true,
                'é' => true,
                'ê' => true,
                'ë' => true,
                'ē' => true,
                'ĕ' => true,
                'ė' => true,
                'ę' => true,
                'ě' => true,
                'ȅ' => true,
                'ȇ' => true,
                'ȩ' => true,
                'е' => true,
                'є' => true,
                'ḕ' => true,
                'ḗ' => true,
                'ḙ' => true,
                'ḛ' => true,
                'ḝ' => true,
                'ẹ' => true,
                'ẻ' => true,
                'ẽ' => true,
                'ế' => true,
                'ề' => true,
                'ể' => true,
                'ễ' => true,
                'ệ' => true,
            ],
            'f' => [
                'ƒ' => true,
                'ф' => true,
            ],
            'g' => [
                'ĝ' => true,
                'ğ' => true,
                'ġ' => true,
                'ģ' => true,
                'г' => true,
            ],
            'h' => [
                'ĥ' => true,
                'ħ' => true,
                'х' => true,
            ],
            'i' => [
                'i' => true,
                'ì' => true,
                'í' => true,
                'î' => true,
                'ï' => true,
                'ĩ' => true,
                'ī' => true,
                'ĭ' => true,
                'į' => true,
                'ǐ' => true,
                'ȉ' => true,
                'ȋ' => true,
                'и' => true,
                'ы' => true,
                'і' => true,
                'ї' => true,
                'ḭ' => true,
                'ḯ' => true,
                'ỉ' => true,
                'ị' => true,
            ],
            'j' => [
                'ĵ' => true,
                'й' => true,
            ],
            'k' => [
                'ķ' => true,
                'ĸ' => true,
                'Ǩ' => true,
                'κ' => true,
            ],
            'l' => [
                'ĺ' => true,
                'ļ' => true,
                'ľ' => true,
                'ŀ' => true,
                'ł' => true,
                'л' => true,
            ],
            'm' => [ 'м' => true ],
            'n' => [
                'ñ' => true,
                'ń' => true,
                'ņ' => true,
                'ň' => true,
                'ʼ' => true,
                'n' => true,
                'ŋ' => true,
                'н' => true,
            ],
            'o' => [
                'o' => true,
                'ò' => true,
                'ó' => true,
                'ô' => true,
                'õ' => true,
                'ö' => true,
                'ø' => true,
                'ō' => true,
                'ŏ' => true,
                'ő' => true,
                'ơ' => true,
                'ǒ' => true,
                'ǫ' => true,
                'ǭ' => true,
                'ǿ' => true,
                'ȍ' => true,
                'ȏ' => true,
                'ȫ' => true,
                'ȭ' => true,
                'ȯ' => true,
                'ȱ' => true,
                'о' => true,
                'ṍ' => true,
                'ṏ' => true,
                'ṑ' => true,
                'ṓ' => true,
                'ọ' => true,
                'ỏ' => true,
                'ố' => true,
                'ồ' => true,
                'ổ' => true,
                'ỗ' => true,
                'ộ' => true,
                'ớ' => true,
                'ờ' => true,
                'ở' => true,
                'ỡ' => true,
                'ợ' => true,
            ],
            'p' => [
                'ƥ' => true,
                'п' => true,
            ],
            'r' => [
                'ŕ' => true,
                'ŗ' => true,
                'ř' => true,
                'р' => true,
            ],
            's' => [
                'ś' => true,
                'ŝ' => true,
                'ş' => true,
                'š' => true,
                'ș' => true,
                'с' => true,
            ],
            't' => [
                'ţ' => true,
                'ť' => true,
                'ŧ' => true,
                'ț' => true,
                'т' => true,
            ],
            'u' => [
                'u' => true,
                'ù' => true,
                'ú' => true,
                'û' => true,
                'ũ' => true,
                'ū' => true,
                'ŭ' => true,
                'ů' => true,
                'ű' => true,
                'ų' => true,
                'ư' => true,
                'ǔ' => true,
                'ǖ' => true,
                'ǘ' => true,
                'ǚ' => true,
                'ǜ' => true,
                'ȕ' => true,
                'ȗ' => true,
                'у' => true,
                'ў' => true,
                'ṳ' => true,
                'ṵ' => true,
                'ṷ' => true,
                'ṹ' => true,
                'ṻ' => true,
                'ụ' => true,
                'ủ' => true,
                'ứ' => true,
                'ừ' => true,
                'ử' => true,
                'ữ' => true,
                'ự' => true,
            ],
            'v' => [ 'в' => true ],
            'w' => [ 'ŵ' => true ],
            'y' => [
                'ý' => true,
                'ÿ' => true,
                'ŷ' => true,
            ],
            'z' => [
                'ź' => true,
                'ż' => true,
                'ž' => true,
                'з' => true,
            ],

            'ch'   => [ 'ч' => true ],
            'dj'   => [ 'đ' => true ],
            'eh'   => [ 'э' => true ],
            'ij'   => [ 'ĳ' => true ],
            'oe'   => [ 'œ' => true ],
            'sh'   => [ 'ш' => true ],
            'shch' => [ 'щ' => true ],
            'ue'   => [ 'ü' => true ],
            'ya'   => [ 'я' => true ],
            'yo'   => [ 'ё' => true ],
            'yu'   => [ 'ю' => true ],
            'zh'   => [ 'ж' => true ],
        ];
    }
}
