<?php

namespace Gzhegow\Lib\Modules\Str\Slugger\Preset;


interface SluggerPresetInterface
{
    /**
     * > эти символы не будут транслитерироваться вовсе
     *
     * @return array<string, bool>
     */
    public function getIgnoreSymbolMap() : array;

    /**
     * > эти символы не будут транслитерироваться по слогам, при этом каждую букву стоит указать отдельно для поддержки `ignoreCase`
     *
     * @return array<string, array<string, string>>
     */
    public function getSequenceMap() : array;

    /**
     * > эти символы будут транслитерироваться один к одному
     *
     * @return array<string, array<string, bool>>
     */
    public function getSymbolMap() : array;
}
