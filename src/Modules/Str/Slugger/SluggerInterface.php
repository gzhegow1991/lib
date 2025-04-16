<?php

namespace Gzhegow\Lib\Modules\Str\Slugger;

use Gzhegow\Lib\Modules\Str\Slugger\Preset\SluggerPresetInterface;


interface SluggerInterface
{
    public function slug(string $string, ?string $delimiter = null, ?array $ignoreSymbols = null, ?string $locale = null) : string;

    public function translit(string $string, ?string $delimiter = null, ?array $ignoreSymbols = null, ?string $locale = null) : string;


    /**
     * @return static
     */
    public function usePresets(?bool $usePresets = null);

    /**
     * @return static
     */
    public function useSymfonySlugger(?bool $useSymfonySlugger = null);

    /**
     * @return static
     */
    public function useIntlTransliterator(?bool $useIntlTransliterator = null);


    /**
     * @return static
     */
    public function selectPresets(array $presets);

    /**
     * @return static
     */
    public function registerPreset(string $name, SluggerPresetInterface $preset);
}
