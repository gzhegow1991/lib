<?php

/**
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Str\Slugger;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Modules\Str\Slugger\Preset\DefaultSluggerPreset;
use Gzhegow\Lib\Modules\Str\Slugger\Preset\SluggerPresetInterface;
use Gzhegow\Lib\Modules\Str\Slugger\PresetRegistry\SluggerPresetRegistry;
use Gzhegow\Lib\Modules\Str\Slugger\PresetRegistry\SluggerPresetRegistryInterface;


class DefaultSlugger implements SluggerInterface
{
    const SYMFONY_SLUGGER_INTERFACE = '\Symfony\Component\String\Slugger\SluggerInterface';


    /**
     * @var SluggerPresetRegistryInterface
     */
    protected $registry;
    /**
     * @var SluggerPresetRegistryInterface
     */
    protected $registryDefault;

    /**
     * @var bool
     */
    protected $usePresets = false;
    /**
     * @var bool
     */
    protected $useSymfonySlugger = false;
    /**
     * @var bool
     */
    protected $useIntlTransliterator = false;

    /**
     * @var string|callable|null
     */
    protected $localeDefault;


    public function __construct(
        ?SluggerPresetRegistryInterface $registry
    )
    {
        Lib::mb();

        $this->registry = $registry ?? new SluggerPresetRegistry();

        $this->registryDefault = clone $this->registry;
        $this->registryDefault->registerPreset('default', new DefaultSluggerPreset());
        $this->registryDefault->selectPresets([ 'default' ]);

        $this->useIntlTransliterator(null);
    }

    public function __clone()
    {
        $this->registry = clone $this->registry;
    }


    /**
     * @return \Symfony\Component\String\Slugger\SluggerInterface
     */
    protected function newSymfonySlugger(
        ?array $ignoreSymbolMap = null,
        ?string $locale = null
    ) : object
    {
        $commands = [
            'composer require symfony/string',
            'composer require symfony/translation-contracts',
        ];

        $symfonyAsciiSluggerClass = '\Symfony\Component\String\Slugger\AsciiSlugger';

        if (! class_exists($symfonyAsciiSluggerClass)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        $localeDefault = null
            ?? $locale
            ?? $this->getLocaleDefault()
            ?? $this->getLocaleDefaultPhp()
            ?? 'en';

        $symbolMapSymfonyForLocale = null;

        if (null !== $ignoreSymbolMap) {
            $symbolMapSymfony = [];
            foreach ( array_keys($ignoreSymbolMap) as $letter ) {
                $symbolMapSymfony[ $letter ] = $letter;
            }

            $symbolMapSymfonyForLocale = [ $localeDefault => $symbolMapSymfony ];
        }

        return new $symfonyAsciiSluggerClass(
            $localeDefault,
            $symbolMapSymfonyForLocale
        );
    }


    /**
     * @return static
     */
    public function usePresets(?bool $usePresets = null)
    {
        $usePresets = $usePresets ?? false;

        $this->usePresets = $usePresets;

        return $this;
    }

    /**
     * @return static
     */
    public function useSymfonySlugger(?bool $useSymfonySlugger = null)
    {
        $classExists = class_exists(static::SYMFONY_SLUGGER_INTERFACE);

        $useSymfonySlugger = $useSymfonySlugger ?? $classExists;

        if ($useSymfonySlugger) {
            if (! $classExists) {
                $commands = [
                    'composer require symfony/string',
                    'composer require symfony/translation-contracts',
                ];

                throw new ComposerException([
                    ''
                    . 'Please, run following commands: '
                    . '[ ' . implode(' ][ ', $commands) . ' ]',
                ]);
            }
        }

        $this->useSymfonySlugger = $useSymfonySlugger;

        return $this;
    }

    /**
     * @return static
     */
    public function useIntlTransliterator(?bool $useIntlTransliterator = null)
    {
        $extensionAndFunctionExists = true
            && extension_loaded('intl')
            && function_exists('transliterator_transliterate');

        $useIntlTransliterator = $useIntlTransliterator ?? $extensionAndFunctionExists;

        if ($useIntlTransliterator) {
            if (! $extensionAndFunctionExists) {
                throw new ComposerException(
                    [
                        ''
                        . 'Missing php extension of function does not exist: '
                        . '[ ' . implode(' ][ ', [ 'ext-intl', 'transliterator_transliterate' ]) . ' ]',
                    ]
                );
            }
        }

        $this->useIntlTransliterator = $useIntlTransliterator;

        return $this;
    }


    /**
     * @return static
     */
    public function selectPresets(array $presets)
    {
        $this->usePresets = true;

        $this->registry->selectPresets($presets);

        return $this;
    }

    /**
     * @return static
     */
    public function registerPreset(string $name, SluggerPresetInterface $preset)
    {
        $this->registry->registerPreset($name, $preset);

        return $this;
    }


    public function getLocaleDefault() : ?string
    {
        $localeDefault = null
            ?? $this->getLocaleDefaultUser()
            ?? $this->getLocaleDefaultPhp();

        return $localeDefault;
    }

    public function getLocaleDefaultUser() : ?string
    {
        $localeDefaultUser = null
            ?? (is_callable($this->localeDefault) ? call_user_func($this->localeDefault) : null)
            ?? (is_string($this->localeDefault) ? $this->localeDefault : null)
            ?? null;

        return $localeDefaultUser;
    }

    public function getLocaleDefaultPhp() : ?string
    {
        $localeDefaultPhp = null;

        if (
            extension_loaded('intl')
            && function_exists('locale_get_default')
        ) {
            $localeDefaultPhp = locale_get_default();

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
            if (! (
                is_string($localeDefault)
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


    public function slug(
        string $string,
        ?string $delimiter = null, ?array $ignoreSymbols = null, ?string $locale = null
    ) : string
    {
        if ('' === $string) {
            return '';
        }

        if (null === $delimiter) {
            $_delimiter = '-';

        } elseif (! Lib::type()->letter($_delimiter, $delimiter)) {
            throw new LogicException(
                [ 'The `delimiter` should be exactly one letter', $delimiter ]
            );
        }

        $slug = $this->translit(
            $string,
            $_delimiter, $ignoreSymbols, $locale
        );

        $delimiterAndSpaceRegex = sprintf('\x{%X}', mb_ord($_delimiter));
        $delimiterAndSpaceRegex = '/[' . $delimiterAndSpaceRegex . ' ]+/iu';

        $slug = preg_replace($delimiterAndSpaceRegex, $_delimiter, $slug);

        $slug = trim($slug, $_delimiter);

        return $slug;
    }

    public function translit(
        string $string,
        ?string $delimiter = null, ?array $ignoreSymbols = null, ?string $locale = null
    ) : string
    {
        if ('' === $string) {
            return '';
        }

        $theType = Lib::type();

        if (null === $delimiter) {
            $_delimiter = '-';

        } elseif (! $theType->letter($_delimiter, $delimiter)) {
            throw new LogicException(
                [ 'The `delimiter` should be exactly one letter', $delimiter ]
            );
        }

        $ignoreSymbolUserList = Lib::php()->to_list($ignoreSymbols);
        $ignoreSymbolUserMap = [];
        foreach ( $ignoreSymbolUserList as $i => $ignoreSymbol ) {
            if (is_string($i)) {
                $ignoreSymbol = $i;
            }

            if (! $theType->string_not_empty($ignoreSymbolString, $ignoreSymbol)) {
                throw new LogicException(
                    [
                        'Each of `ignoreSymbols` should be non-empty string',
                        $ignoreSymbol,
                        $i,
                    ]
                );
            }

            $ignoreSymbolUserMap[ $ignoreSymbolString ] = true;
        }

        if ($this->usePresets) {
            $result = $this->translitPresets(
                $string, $_delimiter, $ignoreSymbolUserMap
            );

        } elseif ($this->useSymfonySlugger) {
            $result = $this->translitSymfonySlugger(
                $string, $_delimiter, $ignoreSymbolUserMap, $locale
            );

        } elseif ($this->useIntlTransliterator) {
            $result = $this->translitIntlTransliterator(
                $string, $_delimiter, $ignoreSymbolUserMap
            );

        } else {
            $result = $this->translitDefault(
                $string, $_delimiter, $ignoreSymbolUserMap
            );
        }

        return $result;
    }


    protected function translitPresets(
        string $string, string $delimiter,
        array $ignoreSymbolUserMap
    ) : string
    {
        if ('' === $string) {
            return '';
        }

        $presets = $this->registry->getPresetsSelected();

        if ([] === $presets) {
            throw new RuntimeException(
                [ 'Unable to ' . __FUNCTION__ . ' | No presets was selected' ]
            );
        }

        [
            $ignoreSymbolMap,
            $sequnceMap,
            $symbolMap,
            $knownSymbolMap,
        ] = $this->registry->getSymbolMapsForPresetsSelected();

        if ([] !== $ignoreSymbolUserMap) {
            $ignoreSymbolMap += $ignoreSymbolUserMap;
            $knownSymbolMap += $ignoreSymbolUserMap;
        }

        $gen = $this->translit_it($string, $ignoreSymbolMap);

        $translit = '';
        foreach ( $gen as [ $chunk, $chunkDelimiter ] ) {
            $chunk = str_replace(
                array_keys($sequnceMap),
                array_values($sequnceMap),
                $chunk
            );

            $chunk = str_replace(
                array_keys($symbolMap),
                array_values($symbolMap),
                $chunk
            );

            $translit .= "{$chunk}{$chunkDelimiter}";
        }

        $knownSymbolMapRegex = array_keys($knownSymbolMap);
        $knownSymbolMapRegex = implode('', $knownSymbolMapRegex);
        $knownSymbolMapRegex = '/[^' . Lib::preg()->preg_quote_ord($knownSymbolMapRegex) . 'a-z0-9 ]/iu';

        $translit = preg_replace($knownSymbolMapRegex, $delimiter, $translit);

        return $translit;
    }

    protected function translitSymfonySlugger(
        string $string, string $delimiter,
        array $ignoreSymbolUserMap, ?string $locale = null
    ) : string
    {
        if ('' === $string) {
            return '';
        }

        $stringObject = new \Symfony\Component\String\BinaryString($string);

        if ($stringObject->isUtf8()) {
            $canUseIntl =
                extension_loaded('intl')
                && function_exists('transliterator_transliterate');

            if (! $canUseIntl) {
                throw new ComposerException(
                    [
                        'Symfony Transliterator works incorectly without `ext-intl` if used on UTF-8 strings',
                        //
                        $string,
                    ]
                );
            }
        }

        $symfonySlugger = $this->newSymfonySlugger($ignoreSymbolUserMap, $locale);

        $translitObject = $symfonySlugger->slug($string, $delimiter);

        $translit = $translitObject->toString();

        return $translit;
    }

    protected function translitIntlTransliterator(
        string $string, string $delimiter,
        array $ignoreSymbolUserMap
    ) : string
    {
        if ('' === $string) {
            return '';
        }

        $rules = [];
        $rules[] = 'NFKD';                       // > split unicode accents and symbols, e.g. "Å" > "A°"
        $rules[] = 'Latin';                      // > convert everything to the Latin charset e.g. "ま" > "ma":
        $rules[] = 'Latin/US-ASCII';             // > convert to ASCII
        $rules[] = 'NFD';                        // > cache
        $rules[] = '[:Nonspacing Mark:] Remove'; // > remove non-printables
        $rules[] = 'NFC';                        // > restore from cache
        $rules = implode('; ', $rules);

        $gen = $this->translit_it($string, $ignoreSymbolUserMap);

        $translit = '';
        foreach ( $gen as [ $chunk, $chunkDelimiter ] ) {
            $chunk = transliterator_transliterate(
                $rules,
                $chunk
            );

            $translit .= "{$chunk}{$chunkDelimiter}";
        }

        $knownSymbolMap = $ignoreSymbolUserMap;

        $knownSymbolMapRegex = array_keys($knownSymbolMap);
        $knownSymbolMapRegex = implode('', $knownSymbolMapRegex);
        $knownSymbolMapRegex = '/[^' . Lib::preg()->preg_quote_ord($knownSymbolMapRegex) . 'a-z0-9 ]/iu';

        $translit = preg_replace($knownSymbolMapRegex, $delimiter, $translit);

        return $translit;
    }

    protected function translitDefault(
        string $string, string $delimiter,
        array $ignoreSymbolUserMap
    ) : string
    {
        if ('' === $string) {
            return '';
        }

        [
            $ignoreSymbolMap,
            $sequnceMap,
            $symbolMap,
            $knownSymbolMap,
        ] = $this->registryDefault->getSymbolMapsForPresetsSelected();

        if ([] !== $ignoreSymbolUserMap) {
            $ignoreSymbolMap += $ignoreSymbolUserMap;
            $knownSymbolMap += $ignoreSymbolUserMap;
        }

        $gen = $this->translit_it($string, $ignoreSymbolMap);

        $translit = '';
        foreach ( $gen as [ $chunk, $chunkDelimiter ] ) {
            $chunk = str_replace(
                array_keys($sequnceMap),
                array_values($sequnceMap),
                $chunk
            );

            $chunk = str_replace(
                array_keys($symbolMap),
                array_values($symbolMap),
                $chunk
            );

            $translit .= "{$chunk}{$chunkDelimiter}";
        }

        $knownSymbolMapRegex = array_keys($knownSymbolMap);
        $knownSymbolMapRegex = implode('', $knownSymbolMapRegex);
        $knownSymbolMapRegex = '/[^' . Lib::preg()->preg_quote_ord($knownSymbolMapRegex) . '0-9 ]/iu';

        $translit = preg_replace($knownSymbolMapRegex, $delimiter, $translit);

        return $translit;
    }


    protected function translit_it(string $string, array $ignoreSymbolMap) : \Generator
    {
        if ([] === $ignoreSymbolMap) {
            yield [ $string, '' ];

        } else {
            $len = mb_strlen($string);

            $prev = 0;
            for ( $i = 0; $i < $len; $i++ ) {
                $letter = mb_substr($string, $i, 1);

                if (isset($ignoreSymbolMap[ $letter ])) {
                    $chunk = mb_substr($string, $prev, $i - $prev);

                    yield [ $chunk, $letter ];

                    $prev = $i + 1;
                }
            }

            if ($prev < $len) {
                $chunk = mb_substr($string, $prev);

                yield [ $chunk, '' ];
            }
        }
    }
}
