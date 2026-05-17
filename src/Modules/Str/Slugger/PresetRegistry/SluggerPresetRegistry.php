<?php

/** @noinspection PhpDocSignatureInspection */

namespace Gzhegow\Lib\Modules\Str\Slugger\PresetRegistry;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Str\Slugger\Preset\SluggerPresetInterface;


class SluggerPresetRegistry implements SluggerPresetRegistryInterface
{
    /**
     * @var array<string, SluggerPresetInterface>
     */
    protected $presets = [];
    /**
     * @var array
     */
    protected $presetsSelected = [];

    /**
     * @var array{
     *   ignoreSymbolMap: array,
     *   sequenceMap: array,
     *   symbolMap: array,
     *   knownSymbolMap: array,
     * }
     */
    protected $symbolMaps;


    public function getPresets() : array
    {
        return $this->presets;
    }

    /**
     * @param array<string, SluggerPresetInterface> $presets
     *
     * @return static
     */
    public function registerPresets(array $presets)
    {
        $this->presets = [];

        foreach ( $presets as $presetName => $preset ) {
            $this->registerPreset($presetName, $preset);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function registerPreset(string $name, SluggerPresetInterface $preset)
    {
        if ( '' === $name ) {
            throw new LogicException(
                [ 'The `name` is should be a non-empty string' ]
            );
        }

        if ( isset($this->presets[$name]) ) {
            throw new RuntimeException(
                [ 'The `name` is already registered', $name ]
            );
        }

        $this->presets[$name] = $preset;

        return $this;
    }


    /**
     * @return array<string, bool>
     */
    public function getPresetsSelected() : array
    {
        return $this->presetsSelected;
    }

    /**
     * @return static
     */
    public function selectPresets(array $names)
    {
        $this->presetsSelected = [];

        foreach ( $names as $i => $name ) {
            if ( ! isset($this->presets[$name]) ) {
                throw new RuntimeException(
                    [ 'Each of `names` should be a registered preset', $name, $i ]
                );
            }

            $this->presetsSelected[$name] = true;
        }

        $this->symbolMaps = null;

        return $this;
    }

    public function getSymbolMapsForPresetsSelected() : array
    {
        if ( [] === $this->presetsSelected ) {
            return [ [], [], [], [] ];
        }

        if ( null === $this->symbolMaps ) {
            $presets = [];
            foreach ( $this->presetsSelected as $preset => $bool ) {
                if ( ! $bool ) continue;

                $presets[$preset] = $this->presets[$preset];
            }

            $this->symbolMaps = $this->prepareSymbolMaps($presets);
        }

        return $this->symbolMaps;
    }


    /**
     * @param SluggerPresetInterface[] $presets
     *
     * @return array
     */
    protected function prepareSymbolMaps(array $presets) : array
    {
        $ignoreSymbolMap = [];
        $sequenceMap = [];
        $symbolMap = [];
        $knownSymbolMap = [];

        // > collect ignore symbols from all presets as first
        foreach ( $presets as $preset ) {
            $ignoreSymbolMapPreset = $preset->getIgnoreSymbolMap();

            if ( [] !== $ignoreSymbolMapPreset ) {
                $knownSymbolMapPreset = [];

                $ignoreSymbolMapPreparedPreset = $this->prepareIgnoreSymbolMap(
                    $ignoreSymbolMapPreset,
                    $knownSymbolMapPreset
                );

                $ignoreSymbolMap += $ignoreSymbolMapPreparedPreset;

                $knownSymbolMap += $knownSymbolMapPreset;
            }
        }

        // > collect replacements from all presets as second
        foreach ( $presets as $preset ) {
            $sequenceMapPreset = $preset->getSequenceMap();

            if ( [] !== $sequenceMapPreset ) {
                $knownSymbolMapPreset = [];

                $sequenceMapPreparedPreset = $this->prepareSequenceMap(
                    $sequenceMapPreset,
                    $ignoreSymbolMap,
                    $knownSymbolMapPreset
                );

                $sequenceMap += $sequenceMapPreparedPreset;

                $knownSymbolMap += $knownSymbolMapPreset;
            }

            $symbolMapPreset = $preset->getSymbolMap();

            if ( [] !== $symbolMapPreset ) {
                $knownSymbolMapPreset = [];

                $symbolMapPreparedPreset = $this->prepareSymbolMap(
                    $symbolMapPreset,
                    $ignoreSymbolMap,
                    $knownSymbolMapPreset
                );

                $symbolMap += $symbolMapPreparedPreset;

                $knownSymbolMap += $knownSymbolMapPreset;
            }
        }

        if ( $knownSymbolMap ) {
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
     * @param array<string, bool>|null $refKnownSymbolMap
     *
     * @return array<string, bool>
     *
     * @noinspection PhpDocSignatureInspection
     */
    protected function prepareIgnoreSymbolMap(
        array $ignoreSymbols,
        ?array &$refKnownSymbolMap = null
    ) : array
    {
        $refKnownSymbolMap = [];

        $theMb = Lib::mb();

        $result = [];

        foreach ( $ignoreSymbols as $i => $letter ) {
            if ( is_string($i) ) {
                $letter = $i;
            }

            $letterArray = null
                ?? (is_array($letter) ? $letter : null)
                ?? (is_string($letter) ? [ $letter ] : null)
                ?? [];

            foreach ( $letterArray as $l ) {
                $letters = $theMb->str_split($l, 1);

                foreach ( $letters as $ll ) {
                    if ( ! isset($result[$ll]) ) {
                        $result[$ll] = true;

                        $refKnownSymbolMap[$ll] = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool>|null $refKnownSymbolMap
     *
     * @return array<string, string>
     *
     * @noinspection PhpDocSignatureInspection
     */
    protected function prepareSequenceMap(
        array $sequenceMap, array $ignoreSymbolMap,
        ?array &$refKnownSymbolMap = null
    ) : array
    {
        $refKnownSymbolMap = [];

        $theItertools = Lib::itertools();
        $theMb = Lib::mb();
        $theType = Lib::type();

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
                $aLetter = $theType->letter($a)->orThrow();

                $aLower = $theMb->mb_strtolower($aLetter);
                $aUpper = $theMb->mb_strtoupper($aLetter);

                if ( false
                    || isset($ignoreSymbolMap[$aLower])
                    || isset($ignoreSymbolMap[$aUpper])
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

                $aCase[$i][] = $aLower;
                $aCase[$i][] = $aUpper;
            }

            $bCase = [];
            foreach ( $bList as $i => $b ) {
                $bCase[$i][] = $theMb->mb_strtolower($b);
                $bCase[$i][] = $theMb->mb_strtoupper($b);
            }

            $aGen = $theItertools->product_it(...$aCase);
            $bGen = $theItertools->product_it(...$bCase);

            $aProductArray = iterator_to_array($aGen);
            $bProductArray = iterator_to_array($bGen);

            foreach ( array_keys($aProductArray) as $i ) {
                $search = implode('', $aProductArray[$i]);
                $replacement = implode('', $bProductArray[$i]);

                if ( isset($result[$search]) ) {
                    throw new LogicException(
                        [
                            'Unable to add sequence due to search string is already registered',
                            $search,
                            $result,
                        ]
                    );
                }

                $result[$search] = $replacement;

                $array = $theMb->str_split($replacement, 1);
                foreach ( $array as $l ) {
                    $refKnownSymbolMap[$l] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool>|null $refKnownSymbolMap
     *
     * @return array<string, string>
     *
     * @noinspection PhpDocSignatureInspection
     */
    protected function prepareSymbolMap(
        array $symbolMap, array $ignoreSymbolMap,
        ?array &$refKnownSymbolMap = null
    ) : array
    {
        $refKnownSymbolMap = [];

        $theMb = Lib::mb();

        $result = [];

        foreach ( $symbolMap as $a => $b ) {
            $aLower = $theMb->mb_strtolower($a);
            $aUpper = $theMb->mb_strtoupper($a);

            if (
                isset($ignoreSymbolMap[$aLower])
                || isset($ignoreSymbolMap[$aUpper])
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

            if ( [] === $bArray ) {
                throw new LogicException(
                    'The `bArray` should be a non-empty array'
                );
            }

            $list = [];
            foreach ( $bArray as $i => $v ) {
                if ( is_string($i) ) {
                    $v = $i;
                }

                $split = $theMb->str_split($v, 1);

                $list = array_merge($list, $split);
            }

            foreach ( $list as $bb ) {
                $bbLower = $theMb->mb_strtolower($bb);
                $bbUpper = $theMb->mb_strtoupper($bb);

                $bbStrsize = strlen($bb);
                $bbLowerStrsize = strlen($bbLower);
                $bbUpperStrsize = strlen($bbUpper);

                $bbStrlen = $theMb->mb_strlen($bb);
                $bbLowerStrlen = $theMb->mb_strlen($bbLower);
                $bbUpperStrlen = $theMb->mb_strlen($bbLower);

                // > example size/length difference when change case: `ß` -> `SS`
                if ( false
                    || ($bbStrsize !== $bbLowerStrsize)
                    || ($bbStrsize !== $bbUpperStrsize)
                    || ($bbStrlen !== $bbLowerStrlen)
                    || ($bbStrlen !== $bbUpperStrlen)
                ) {
                    throw new LogicException(
                        [
                            'Changing case forces unexpected size/length difference, you should move this symbol to `sequenceMap`',
                            [ $a => $bb ],
                            [ $bb, $bbLower, $bbUpper ],
                        ]
                    );
                }

                if ( false
                    || isset($result[$bbLower])
                    || isset($result[$bbUpper])
                ) {
                    throw new LogicException(
                        [
                            'Unable to add letter to results of `symbolMap` due to letter is known as source',
                        ]
                    );
                }

                $result[$bbLower] = $aLower;
                $result[$bbUpper] = $aUpper;

                $array = $theMb->str_split($aLower, 1);
                foreach ( $array as $l ) {
                    $refKnownSymbolMap[$l] = true;
                }

                $array = $theMb->str_split($aUpper, 1);
                foreach ( $array as $l ) {
                    $refKnownSymbolMap[$l] = true;
                }
            }
        }

        return $result;
    }
}
