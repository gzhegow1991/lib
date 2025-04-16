<?php

namespace Gzhegow\Lib\Modules\Social\PhoneRegionDetector;

interface PhoneRegionDetectorInterface
{
    public function detectRegion(string $telDigits) : ?string;
}
