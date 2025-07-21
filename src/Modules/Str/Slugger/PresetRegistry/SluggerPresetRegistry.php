<?php

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


    /**
     * @return static
     */
    public function selectPresets(array $names)
    {
        $this->presetsSelected = [];

        foreach ( $names as $i => $name ) {
            if (! isset($this->presets[ $name ])) {
                throw new RuntimeException(
                    [ 'Each of `names` should be a registered preset', $name, $i ]
                );
            }

            $this->presetsSelected[ $name ] = true;
        }

        $this->symbolMaps = null;

        return $this;
    }

    public function getPresetsSelected() : array
    {
        return $this->presetsSelected;
    }

    public function getSymbolMapsForPresetsSelected() : array
    {
        if ([] === $this->presetsSelected) {
            return [ [], [], [], [] ];
        }

        if (null === $this->symbolMaps) {
            $presets = [];
            foreach ( $this->presetsSelected as $preset => $bool ) {
                $presets[ $preset ] = $this->presets[ $preset ];
            }

            $this->symbolMaps = $this->prepareSymbolMaps($presets);
        }

        return $this->symbolMaps;
    }


    /**
     * @return static
     */
    public function registerPreset(string $name, SluggerPresetInterface $preset)
    {
        if ('' === $name) {
            throw new LogicException(
                [ 'The `name` is should be a non-empty string' ]
            );
        }

        if (isset($this->presets[ $name ])) {
            throw new RuntimeException(
                [ 'The `name` is already registered', $name ]
            );
        }

        $this->presets[ $name ] = $preset;

        return $this;
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

            if ([] !== $ignoreSymbolMapPreset) {
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

            if ([] !== $sequenceMapPreset) {
                $knownSymbolMapPreset = [];

                $ignoreSymbolMapPreparedPreset = $this->prepareSequenceMap(
                    $sequenceMapPreset,
                    $knownSymbolMapPreset
                );

                $ignoreSymbolMap += $ignoreSymbolMapPreparedPreset;
                $knownSymbolMap += $knownSymbolMapPreset;
            }

            $symbolMapPreset = $preset->getSymbolMap();

            if ([] !== $symbolMapPreset) {
                $knownSymbolMapPreset = [];

                $ignoreSymbolMapPreparedPreset = $this->prepareSymbolMap(
                    $symbolMapPreset,
                    $knownSymbolMapPreset
                );

                $ignoreSymbolMap += $ignoreSymbolMapPreparedPreset;
                $knownSymbolMap += $knownSymbolMapPreset;
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
     * @param array<string, bool> $refKnownSymbolMap
     *
     * @return array<string, bool>
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
            if (is_string($i)) {
                $letter = $i;
            }

            $letterArray = null
                ?? (is_array($letter) ? $letter : null)
                ?? (is_string($letter) ? [ $letter ] : null)
                ?? [];

            foreach ( $letterArray as $l ) {
                $letters = $theMb->str_split($l, 1);

                foreach ( $letters as $ll ) {
                    if (! isset($result[ $ll ])) {
                        $result[ $ll ] = true;

                        $refKnownSymbolMap[ $ll ] = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool> $refKnownSymbolMap
     *
     * @return array<string, string>
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

                $aLower = mb_strtolower($aLetter);
                $aUpper = mb_strtoupper($aLetter);

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
                    $refKnownSymbolMap[ $l ] = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, bool> $refKnownSymbolMap
     *
     * @return array<string, string>
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
            $aLower = mb_strtolower($a);
            $aUpper = mb_strtoupper($a);

            if (
                isset($ignoreSymbolMap[ $aLower ])
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

            if ([] === $bArray) {
                throw new LogicException(
                    'The `bArray` should be a non-empty array'
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
                    $refKnownSymbolMap[ $l ] = true;
                }

                $array = $theMb->str_split($aUpper, 1);
                foreach ( $array as $l ) {
                    $refKnownSymbolMap[ $l ] = true;
                }
            }
        }

        return $result;
    }
}
