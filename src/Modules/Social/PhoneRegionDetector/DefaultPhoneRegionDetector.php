<?php

namespace Gzhegow\Lib\Modules\Social\PhoneRegionDetector;

class DefaultPhoneRegionDetector implements PhoneRegionDetectorInterface
{
    public function detectRegion(string $telDigits) : ?string
    {
        $region = null
            ?? $this->detectRegionBy($telDigits)
            //
            ?? $this->detectRegionRu($telDigits)
            ?? $this->detectRegionKz($telDigits);

        return $region;
    }


    protected function detectRegionBy(string $telDigits) : ?string
    {
        if (strlen($telDigits) === 12) {
            if (0 === strpos($telDigits, '375')) {
                return 'BY';
            }
        }

        return null;
    }


    protected function detectRegionKz(string $telDigits) : ?string
    {
        if (strlen($telDigits) === 11) {
            if ($telDigits[ 0 ] === '7') {
                if (in_array($telDigits[ 1 ], [ 6, 7 ])) {
                    return 'KZ';
                }
            }
        }

        return null;
    }

    protected function detectRegionRu(string $telDigits) : ?string
    {
        $len = strlen($telDigits);

        if ($len === 11) {
            if ($telDigits[ 0 ] === '7') {
                if (! in_array($telDigits[ 1 ], [ 6, 7 ])) {
                    return 'RU';
                }
            }

            if (($telDigits[ 0 ] === '8')) {
                if (($telDigits[ 1 ] === '9')) {
                    return 'RU';
                }
            }

        } elseif ($len === 10) {
            if (($telDigits[ 0 ] === '9')) {
                return 'RU';
            }
        }

        return null;
    }
}
