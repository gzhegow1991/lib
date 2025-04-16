<?php

namespace Gzhegow\Lib\Modules\Str\Slugger\PresetRegistry;

use Gzhegow\Lib\Modules\Str\Slugger\Preset\SluggerPresetInterface;


interface SluggerPresetRegistryInterface
{
    /**
     * @return static
     */
    public function selectPresets(array $names);

    public function getPresetsSelected() : array;

    public function getSymbolMapsForPresetsSelected() : array;


    /**
     * @return static
     */
    public function registerPreset(string $name, SluggerPresetInterface $preset);
}
