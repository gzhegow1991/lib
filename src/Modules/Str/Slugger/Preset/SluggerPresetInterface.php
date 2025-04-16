<?php

namespace Gzhegow\Lib\Modules\Str\Slugger\Preset;


interface SluggerPresetInterface
{
    /**
     * @return array<string, bool>
     */
    public function getIgnoreSymbolMap() : array;

    /**
     * @return array<string, array<string, string>>
     */
    public function getSequenceMap() : array;

    /**
     * @return array<string, array<string, bool>>
     */
    public function getSymbolMap() : array;
}
