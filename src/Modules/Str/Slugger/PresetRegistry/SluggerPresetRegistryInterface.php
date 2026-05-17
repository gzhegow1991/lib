<?php

/** @noinspection PhpDocSignatureInspection */

namespace Gzhegow\Lib\Modules\Str\Slugger\PresetRegistry;

use Gzhegow\Lib\Modules\Str\Slugger\Preset\SluggerPresetInterface;


interface SluggerPresetRegistryInterface
{
    /**
     * @return array<string, SluggerPresetInterface>
     */
    public function getPresets() : array;

    /**
     * @param array<string, SluggerPresetInterface> $presets
     *
     * @return static
     */
    public function registerPresets(array $presets);

    /**
     * @return static
     */
    public function registerPreset(string $name, SluggerPresetInterface $preset);


    /**
     * @return array<string, bool>
     */
    public function getPresetsSelected() : array;

    /**
     * @return static
     */
    public function selectPresets(array $names);

    public function getSymbolMapsForPresetsSelected() : array;
}
