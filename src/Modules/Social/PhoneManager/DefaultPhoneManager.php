<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Social\PhoneManager;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Modules\Social\PhoneRegionDetector\DefaultPhoneRegionDetector;
use Gzhegow\Lib\Modules\Social\PhoneRegionDetector\PhoneRegionDetectorInterface;


class DefaultPhoneManager implements PhoneManagerInterface
{
    const GIGGSEY_PHONE_NUMBER_UTIL_CLASS                 = '\libphonenumber\PhoneNumberUtil';
    const GIGGSEY_PHONE_NUMBER_OFFLINE_GEOCODER_CLASS     = '\libphonenumber\geocoding\PhoneNumberOfflineGeocoder';
    const GIGGSEY_PHONE_NUMBER_TO_CARRIER_MAPPER_CLASS    = '\libphonenumber\PhoneNumberToCarrierMapper';
    const GIGGSEY_PHONE_NUMBER_TO_TIME_ZONES_MAPPER_CLASS = '\libphonenumber\PhoneNumberToTimeZonesMapper';


    /**
     * @var PhoneRegionDetectorInterface
     */
    protected $regionDetector;

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected $giggseyPhoneNumberUtil;
    /**
     * @var \libphonenumber\geocoding\PhoneNumberOfflineGeocoder
     */
    protected $giggseyPhoneNumberOfflineGeocoder;
    /**
     * @var \libphonenumber\PhoneNumberToCarrierMapper
     */
    protected $giggseyPhoneNumberToCarrierMapper;
    /**
     * @var \libphonenumber\PhoneNumberToTimeZonesMapper
     */
    protected $giggseyPhoneNumberToTimeZonesMapper;

    /**
     * @var string[]
     */
    protected $phoneFakeRegexIndex = [
        '/^[+]7999/'  => true,
        '/^[+]37599/' => true,
    ];
    /**
     * @var bool
     */
    protected $usePhoneFakeDatelike = false;

    /**
     * @var bool
     */
    protected $useRegionDetector = true;
    /**
     * @var bool
     */
    protected $useRegionAutoDetection = false;


    public function __construct(
        ?PhoneRegionDetectorInterface $regionDetector
    )
    {
        $this->regionDetector = $regionDetector ?? new DefaultPhoneRegionDetector();
    }

    public function __clone()
    {
        $this->regionDetector = clone $this->regionDetector;
    }


    /**
     * @return \libphonenumber\PhoneNumberUtil
     */
    protected function newGiggseyPhoneNumberUtil() : object
    {
        $commands = [
            'composer require giggsey/libphonenumber-for-php',
        ];

        if (! class_exists($giggseyPhoneNumberUtilClass = static::GIGGSEY_PHONE_NUMBER_UTIL_CLASS)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $giggseyPhoneNumberUtilClass::{'getInstance'}();
    }

    /**
     * @return \libphonenumber\PhoneNumberUtil
     */
    protected function getGiggseyPhoneNumberUtil() : object
    {
        return $this->giggseyPhoneNumberUtil = null
            ?? $this->giggseyPhoneNumberUtil
            ?? $this->newGiggseyPhoneNumberUtil();
    }


    /**
     * @return \libphonenumber\geocoding\PhoneNumberOfflineGeocoder
     */
    protected function newGiggseyPhoneNumberOfflineGeocoder() : object
    {
        $commands = [
            'composer require giggsey/libphonenumber-for-php',
        ];

        if (! class_exists($giggseyPhoneNumberOfflineGeocoderClass = static::GIGGSEY_PHONE_NUMBER_OFFLINE_GEOCODER_CLASS)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $giggseyPhoneNumberOfflineGeocoderClass::{'getInstance'}();
    }

    /**
     * @return \libphonenumber\geocoding\PhoneNumberOfflineGeocoder
     */
    protected function getGiggseyPhoneNumberOfflineGeocoder() : object
    {
        return $this->giggseyPhoneNumberOfflineGeocoder = null
            ?? $this->giggseyPhoneNumberOfflineGeocoder
            ?? $this->newGiggseyPhoneNumberOfflineGeocoder();
    }


    /**
     * @return \libphonenumber\PhoneNumberToCarrierMapper
     */
    protected function newGiggseyPhoneNumberToCarrierMapper() : object
    {
        $commands = [
            'composer require giggsey/libphonenumber-for-php',
        ];

        if (! class_exists($giggseyPhoneNumberToCarrierMapperClass = static::GIGGSEY_PHONE_NUMBER_TO_CARRIER_MAPPER_CLASS)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $giggseyPhoneNumberToCarrierMapperClass::{'getInstance'}();
    }

    /**
     * @return \libphonenumber\PhoneNumberToCarrierMapper
     */
    protected function getGiggseyPhoneNumberToCarrierMapper() : object
    {
        return $this->giggseyPhoneNumberToCarrierMapper = null
            ?? $this->giggseyPhoneNumberToCarrierMapper
            ?? $this->newGiggseyPhoneNumberToCarrierMapper();
    }


    /**
     * @return \libphonenumber\PhoneNumberToTimeZonesMapper
     */
    protected function newGiggseyPhoneNumberToTimeZonesMapper() : object
    {
        $commands = [
            'composer require giggsey/libphonenumber-for-php',
        ];

        if (! class_exists($giggseyPhoneNumberToTimeZonesMapperClass = static::GIGGSEY_PHONE_NUMBER_TO_TIME_ZONES_MAPPER_CLASS)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $giggseyPhoneNumberToTimeZonesMapperClass::{'getInstance'}();
    }

    /**
     * @return \libphonenumber\PhoneNumberToTimeZonesMapper
     */
    protected function getGiggseyPhoneNumberToTimeZonesMapper() : object
    {
        return $this->giggseyPhoneNumberToTimeZonesMapper = null
            ?? $this->giggseyPhoneNumberToTimeZonesMapper
            ?? $this->newGiggseyPhoneNumberToTimeZonesMapper();
    }


    /**
     * @return static
     */
    public function setPhoneFakeRegexes(?array $regexList)
    {
        if (null === $regexList) {
            $this->phoneFakeRegexIndex = [
                '/^[+]7999/'  => true,
                '/^[+]37599/' => true,
            ];

        } else {
            $this->phoneFakeRegexIndex = [];

            $this->addPhoneFakeRegexes($regexList);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addPhoneFakeRegexes(array $regexList)
    {
        if ([] === $regexList) {
            return $this;
        }

        $theType = Lib::type();

        foreach ( $regexList as $regex ) {
            $regexValid = $theType->regex($regex)->orThrow();

            if (! isset($this->phoneFakeRegexIndex[ $regexValid ])) {
                $this->phoneFakeRegexIndex[ $regexValid ] = true;
            }
        }

        return $this;
    }


    /**
     * @return static
     */
    public function usePhoneFakeDatelike(?bool $usePhoneFakeDatelike = null)
    {
        $usePhoneFakeDatelike = $usePhoneFakeDatelike ?? false;

        $this->usePhoneFakeDatelike = $usePhoneFakeDatelike;

        return $this;
    }


    /**
     * @return static
     */
    public function useRegionDetector(?bool $useRegionDetector = null)
    {
        $useRegionDetector = $useRegionDetector ?? true;

        $this->useRegionDetector = $useRegionDetector;

        return $this;
    }

    /**
     * @return static
     */
    public function useRegionAutoDetection(?bool $useRegionAutoDetection = null)
    {
        $useRegionAutoDetection = $useRegionAutoDetection ?? false;

        $this->useRegionAutoDetection = $useRegionAutoDetection;

        return $this;
    }


    public function parsePhone($value, ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string
    {
        $refTel = null;

        $telParsed = $this->parseTel($value, $refTelDigits, $refTelPlus);

        $allowedSymbolsRegex = ''
            . '[^'
            . '0-9'
            . preg_quote(' ()-', '/')
            . ']';

        $phone = preg_replace("/{$allowedSymbolsRegex}/", '', $value);

        if ($refTelPlus) {
            $phone = '+' . $phone;
        }

        $refTel = $telParsed;

        return $phone;
    }

    public function parsePhoneFake($value, ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string
    {
        $refTel = null;

        $telFake = $this->parseTelFake($value, $refTelDigits, $refTelPlus);

        $allowedSymbolsRegex = ''
            . '[^'
            . '0-9'
            . preg_quote(' ()-', '/')
            . ']';

        $phoneFake = preg_replace("/{$allowedSymbolsRegex}/", '', $value);

        if ($refTelPlus) {
            $phoneFake = '+' . $phoneFake;
        }

        $refTel = $telFake;

        return $phoneFake;
    }

    public function parsePhoneNonFake($value, ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string
    {
        $refTel = null;

        $telNonFake = $this->parseTelNonFake($value, $refTelDigits, $refTelPlus);

        $allowedSymbolsRegex = ''
            . '[^'
            . '0-9'
            . preg_quote(' ()-', '/')
            . ']';

        $phoneNonFake = preg_replace("/{$allowedSymbolsRegex}/", '', $value);

        if ($refTelPlus) {
            $phoneNonFake = '+' . $phoneNonFake;
        }

        $refTel = $telNonFake;

        return $phoneNonFake;
    }

    public function parsePhoneReal(
        $value, ?string $region = '',
        ?string &$refRegionDetected = null,
        ?string &$refTel = null, ?string &$refTelDigits = null, ?string &$refTelPlus = null
    ) : string
    {
        $refTel = null;

        $telNonFake = $this->parseTelNonFake(
            $value,
            $refTelDigits, $refTelPlus
        );

        $phoneNumberObject = $this->parsePhoneNumber(
            $telNonFake, $region,
            $refRegionDetected
        );

        $formatted = $this->formatInternational($phoneNumberObject);

        $refTel = $telNonFake;

        return $formatted;
    }


    public function parseTel($value, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string
    {
        $refTelDigits = null;
        $refTelPlus = null;

        $theType = Lib::type();

        if (is_a($value, '\libphonenumber\PhoneNumber')) {
            $tel = $this->formatE164($value);

            $isPlus = ($tel[ 0 ] === '+');

            $telDigitsString = $isPlus ? substr($tel, 1) : $tel;
            $telPlusString = $isPlus ? '+' : '';

        } else {
            $valueStringNotEmpty = $theType->string_not_empty($value)->orThrow();

            $tel = preg_replace('/[^0-9]/', '', $valueStringNotEmpty);

            if ('' === $tel) {
                throw new LogicException(
                    [ 'The `tel` should be a valid phone number', $value ]
                );
            }

            if (strlen($tel) > 15) {
                throw new LogicException(
                    [ 'The `tel` length should be less than 15 (16 - plus sign) according E164', $value ]
                );
            }

            $isPlus = ($valueStringNotEmpty[ 0 ] === '+');

            $telPlusString = $isPlus ? '+' : '';
            $telDigitsString = $tel;
        }

        $refTelDigits = $telDigitsString;
        $refTelPlus = $telPlusString;

        $tel = $isPlus
            ? '+' . $telDigitsString
            : $telDigitsString;

        return $tel;
    }

    public function parseTelFake($value, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string
    {
        $telString = $this->parseTel($value, $refTelDigits, $refTelPlus);

        $isFake = null;

        foreach ( $this->phoneFakeRegexIndex as $regexp => $bool ) {
            if (preg_match($regexp, $telString)) {
                $isFake = true;

                break;
            }
        }

        if (null === $isFake) {
            if ($this->usePhoneFakeDatelike) {
                try {
                    $dt = \DateTime::createFromFormat('YmdHis', $refTelDigits);

                    if (false !== $dt) {
                        $isFake = true;
                    }
                }
                catch ( \Throwable $e ) {
                }
            }
        }

        if (null === $isFake) {
            throw new RuntimeException(
                [
                    'The `value` should be a fake phone number',
                    $value,
                ]
            );
        }

        return $telString;
    }

    public function parseTelNonFake($value, ?string &$refTelDigits = null, ?string &$refTelPlus = null) : string
    {
        $telString = $this->parseTel($value, $refTelDigits, $refTelPlus);

        foreach ( $this->phoneFakeRegexIndex as $regexp => $bool ) {
            if (preg_match($regexp, $telString)) {
                throw new RuntimeException(
                    [
                        'The `value` must not match any of `phoneFakeRegexIndex` items',
                        $value,
                    ]
                );
            }
        }

        if ($this->usePhoneFakeDatelike) {
            try {
                $dt = \DateTime::createFromFormat('+YmdHis', $telString);

                if (false !== $dt) {
                    throw new RuntimeException(
                        [
                            'The `value` should not be a datelike phone',
                            $value,
                        ]
                    );
                }
            }
            catch ( \Throwable $e ) {
            }
        }

        return $telString;
    }

    public function parseTelReal(
        $value, ?string $region = '',
        ?string &$refRegionDetected = null,
        ?string &$refTelDigits = null, ?string &$refTelPlus = null
    ) : string
    {
        $telNonFake = $this->parseTelNonFake(
            $value,
            $refTelDigits, $refTelPlus
        );

        $phoneNumberObject = $this->parsePhoneNumber(
            $telNonFake, $region,
            $refRegionDetected
        );

        $formatted = $this->formatE164($phoneNumberObject);

        return $formatted;
    }


    /**
     * @return object|\libphonenumber\PhoneNumber
     */
    public function parsePhoneNumber(
        $value, ?string $region = '',
        ?string &$refRegionDetected = null
    ) : object
    {
        $refRegionDetected = null;

        if ($value instanceof \libphonenumber\PhoneNumber) {
            $phoneNumber = $value;

            $regionString = $value->getCountryCode();

        } else {
            $phone = $this->parsePhone($value, $tel, $telDigits);

            if ('' === $region) {
                $regionString = null;

                if ($this->useRegionDetector) {
                    if (null === $regionString) {
                        $regionString = $this->regionDetector->detectRegion($telDigits);
                    }
                }
                if ($this->useRegionAutoDetection) {
                    if (null === $regionString) {
                        $regionString = \libphonenumber\PhoneNumberUtil::UNKNOWN_REGION;
                    }
                }

                if (null === $regionString) {
                    throw new RuntimeException(
                        [
                            'Unable to detect region',
                            $value,
                        ]
                    );
                }

            } else {
                $regionString = $region;
            }

            try {
                $phoneNumberUtil = $this->getGiggseyPhoneNumberUtil();

                $phoneNumber = $phoneNumberUtil
                    ->parse($phone, $regionString)
                ;
            }
            catch ( \libphonenumber\NumberParseException $e ) {
                throw new RuntimeException(
                    [ 'Unable to ' . __FUNCTION__, $e ]
                );
            }
        }

        $refRegionDetected = $regionString;

        return $phoneNumber;
    }


    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatShort($phoneNumber, ?string $region = '', array $fallback = []) : string
    {
        try {
            $formatted = $this->formatE164($phoneNumber, $region);
        }
        catch ( \Throwable $e ) {
            if ([] === $fallback) {
                throw new RuntimeException(
                    'Unable to ' . __FUNCTION__, $e
                );
            }

            [ $formatted ] = $fallback;
        }

        return $formatted;
    }

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatLong($phoneNumber, ?string $region = '', array $fallback = []) : string
    {
        try {
            $formatted = $this->formatInternational($phoneNumber, $region);
        }
        catch ( \Throwable $e ) {
            if ([] === $fallback) {
                throw new RuntimeException(
                    'Unable to ' . __FUNCTION__, $e
                );
            }

            [ $formatted ] = $fallback;
        }

        return $formatted;
    }

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatHref($phoneNumber, ?string $region = '', array $fallback = []) : string
    {
        try {
            $formatted = $this->formatRFC3966($phoneNumber, $region);
        }
        catch ( \Throwable $e ) {
            if ([] === $fallback) {
                throw new RuntimeException(
                    'Unable to ' . __FUNCTION__, $e
                );
            }

            [ $formatted ] = $fallback;
        }

        [ , $phone ] = explode(':', $formatted, 2) + [ '', '' ];

        $formatted = 'tel:' . urlencode($phone);

        return $formatted;
    }


    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatE164($phoneNumber, ?string $region = '') : ?string
    {
        if ($phoneNumber instanceof \libphonenumber\PhoneNumber) {
            $phoneNumberObject = $phoneNumber;

        } else {
            $tel = $this->parseTelNonFake($phoneNumber);

            $phoneNumberObject = $this->parsePhoneNumber(
                $tel, $region
            );
        }

        $formatted = $this->giggseyPhoneNumberUtil->format(
            $phoneNumberObject,
            \libphonenumber\PhoneNumberFormat::E164
        );

        return $formatted;
    }

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatRFC3966($phoneNumber, ?string $region = '') : ?string
    {
        if ($phoneNumber instanceof \libphonenumber\PhoneNumber) {
            $phoneNumberObject = $phoneNumber;

        } else {
            $tel = $this->parseTelNonFake($phoneNumber);

            $phoneNumberObject = $this->parsePhoneNumber(
                $tel, $region
            );
        }

        $formatted = $this->giggseyPhoneNumberUtil->format(
            $phoneNumberObject,
            \libphonenumber\PhoneNumberFormat::RFC3966
        );

        return $formatted;
    }

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatInternational($phoneNumber, ?string $region = '') : ?string
    {
        if ($phoneNumber instanceof \libphonenumber\PhoneNumber) {
            $phoneNumberObject = $phoneNumber;

        } else {
            $tel = $this->parseTelNonFake($phoneNumber);

            $phoneNumberObject = $this->parsePhoneNumber(
                $tel, $region
            );
        }

        $formatted = $this->giggseyPhoneNumberUtil->format(
            $phoneNumberObject,
            \libphonenumber\PhoneNumberFormat::INTERNATIONAL
        );

        return $formatted;
    }

    /**
     * @param string|\libphonenumber\PhoneNumber $phoneNumber
     */
    public function formatNational($phoneNumber, ?string $region = '') : ?string
    {
        if ($phoneNumber instanceof \libphonenumber\PhoneNumber) {
            $phoneNumberObject = $phoneNumber;

        } else {
            $tel = $this->parseTelNonFake($phoneNumber);

            $phoneNumberObject = $this->parsePhoneNumber(
                $tel, $region
            );
        }

        $formatted = $this->giggseyPhoneNumberUtil->format(
            $phoneNumberObject,
            \libphonenumber\PhoneNumberFormat::NATIONAL
        );

        return $formatted;
    }


    public function detectRegion($phone) : string
    {
        $tel = $this->parseTelNonFake($phone, $telDigits);

        $regionString = null;

        if ($this->useRegionDetector) {
            if (null === $regionString) {
                $regionString = $this->regionDetector->detectRegion($telDigits);
            }
        }

        if ($this->useRegionAutoDetection) {
            if (null === $regionString) {
                $this->parsePhoneNumber(
                    $tel, \libphonenumber\PhoneNumberUtil::UNKNOWN_REGION,
                    $regionString
                );
            }
        }

        if (null === $regionString) {
            throw new RuntimeException(
                [
                    'Unable to detect region',
                    $phone,
                ]
            );
        }

        return $regionString;
    }


    public function getTimezonesForPhone($phoneNumber, $timezoneWildcards = null, ?string $region = '') : array
    {
        $thePhp = Lib::php();

        $timezoneWildcardsList = $thePhp->to_list($timezoneWildcards);

        $phoneNumberObject = $this->parsePhoneNumber(
            $phoneNumber, $region
        );

        $phoneNumberToTimeZonesMapper = $this->getGiggseyPhoneNumberToTimeZonesMapper();

        $timezones = $phoneNumberToTimeZonesMapper->getTimeZonesForNumber($phoneNumberObject);

        if ([] !== $timezoneWildcardsList) {
            $wildcards = [];
            foreach ( $timezoneWildcardsList as $i => $wildcard ) {
                if (is_string($i)) {
                    $wildcard = $i;
                }

                $wildcards[ $wildcard ] = true;
            }
            $wildcards = array_keys($wildcards);

            foreach ( $timezones as $i => $timezoneName ) {
                $isMatch = false;
                foreach ( $wildcards as $wildcard ) {
                    if (false !== strpos($timezoneName, $wildcard)) {
                        $isMatch = true;

                        break;
                    }
                }

                if (! $isMatch) {
                    unset($timezones[ $i ]);
                }
            }
        }

        return $timezones;
    }

    public function getLocationNameForPhone($phoneNumber, ?string $region = '') : string
    {
        $_phoneNumber = $this->parsePhoneNumber(
            $phoneNumber, $region,
            $regionParsed
        );

        $phoneNumberOfflineGeocoder = $this->getGiggseyPhoneNumberOfflineGeocoder();

        $location = $phoneNumberOfflineGeocoder
            ->getDescriptionForValidNumber($_phoneNumber, 'en_US')
        ;

        $location = $location ?: '{{ Unknown }}';

        $location = "{$regionParsed} / {$location}";

        return $location;
    }

    public function getOperatorNameForPhone($phoneNumber, ?string $region = '') : string
    {
        $_phoneNumber = $this->parsePhoneNumber(
            $phoneNumber, $region
        );

        $phoneNumberToCarrierMapper = $this->getGiggseyPhoneNumberToCarrierMapper();

        $operatorName = $phoneNumberToCarrierMapper
            ->getNameForValidNumber($_phoneNumber, 'en')
        ;

        $operatorName = $operatorName ?: "{{ Unknown }}";

        return $operatorName;
    }
}
