<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Social\PhoneManager;

interface PhoneManagerInterface
{
    /**
     * @return static
     */
    public function setPhoneFakeRegexes(?array $regexList);

    /**
     * @return static
     */
    public function addPhoneFakeRegexes(array $regexList);


    /**
     * @return static
     */
    public function usePhoneFakeDatelike(?bool $usePhoneFakeDatelike = null);


    /**
     * @return static
     */
    public function useRegionDetector(?bool $useRegionDetector = null);

    /**
     * @return static
     */
    public function useRegionAutoDetection(?bool $useRegionAutoDetection = null);


    public function parsePhone($value, ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string;

    public function parsePhoneFake($value, ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string;

    public function parsePhoneNonFake($value, ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string;

    public function parsePhoneReal(
        $value, ?string $region = '',
        ?string &$refRegionDetected = null,
        ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null
    ) : string;


    public function parseTel($value, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string;

    public function parseTelFake($value, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string;

    public function parseTelNonFake($value, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string;

    public function parseTelReal(
        $value, ?string $region = '',
        ?string &$refRegionDetected = null,
        ?string &$refTelDigits = null, ?string &$refTelPlus = null
    ) : string;


    /**
     * @return object|\libphonenumber\PhoneNumber
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function parsePhoneNumber(
        $value, ?string $region = '',
        ?string &$refRegionDetected = null
    ) : object;


    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatShort($phoneNumber, ?string $region = '', array $fallback = []) : string;

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatLong($phoneNumber, ?string $region = '', array $fallback = []) : string;

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatHref($phoneNumber, ?string $region = '', array $fallback = []) : string;


    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatE164($phoneNumber, ?string $region = '') : ?string;

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatRFC3966($phoneNumber, ?string $region = '') : ?string;

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatInternational($phoneNumber, ?string $region = '') : ?string;

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatNational($phoneNumber, ?string $region = '') : ?string;


    public function detectRegion($phone) : string;


    public function getTimezonesForPhone($phoneNumber, $timezoneWildcards = null, ?string $region = '') : array;

    public function getLocationNameForPhone($phoneNumber, ?string $region = '') : string;

    public function getOperatorNameForPhone($phoneNumber, ?string $region = '') : string;
}
