<?php

/**
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

        $libphonenumberPhoneNumberUtilClass = '\libphonenumber\PhoneNumberUtil';

        if (! class_exists($libphonenumberPhoneNumberUtilClass)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $libphonenumberPhoneNumberUtilClass::getInstance();
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

        $libphonenumberPhoneNumberOfflineGeocoderClass = '\libphonenumber\geocoding\PhoneNumberOfflineGeocoder';

        if (! class_exists($libphonenumberPhoneNumberOfflineGeocoderClass)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $libphonenumberPhoneNumberOfflineGeocoderClass::getInstance();
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

        $libphonenumberPhoneNumberToCarrierMapperClass = '\libphonenumber\PhoneNumberToCarrierMapper';

        if (! class_exists($libphonenumberPhoneNumberToCarrierMapperClass)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $libphonenumberPhoneNumberToCarrierMapperClass::getInstance();
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

        $libphonenumberPhoneNumberToTimeZonesMapperClass = '\libphonenumber\PhoneNumberToTimeZonesMapper';

        if (! class_exists($libphonenumberPhoneNumberToTimeZonesMapperClass)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return $libphonenumberPhoneNumberToTimeZonesMapperClass::getInstance();
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

        foreach ( $regexList as $i => $regex ) {
            if (! $theType->regex($regexp, $regex)) {
                throw new RuntimeException(
                    [
                        'Each of `regexList` should be valid regular expression',
                        $regex,
                        $i,
                    ]
                );
            }

            if (! isset($this->phoneFakeRegexIndex[ $regexp ])) {
                $this->phoneFakeRegexIndex[ $regexp ] = true;
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


    public function parsePhone($value, ?string &$tel = null, ?string &$telDigits = null, ?string &$telPlus = null) : string
    {
        $tel = null;

        $telParsed = $this->parseTel($value, $telDigits, $telPlus);

        $allowedSymbolsRegex = ''
            . '[^'
            . '0-9'
            . preg_quote(' ()-', '/')
            . ']';

        $phone = preg_replace("/{$allowedSymbolsRegex}/", '', $value);

        if ($telPlus) {
            $phone = '+' . $phone;
        }

        $tel = $telParsed;

        return $phone;
    }

    public function parsePhoneFake($value, ?string &$tel = null, ?string &$telDigits = null, ?string &$telPlus = null) : string
    {
        $tel = null;

        $telFake = $this->parseTelFake($value, $telDigits, $telPlus);

        $allowedSymbolsRegex = ''
            . '[^'
            . '0-9'
            . preg_quote(' ()-', '/')
            . ']';

        $phoneFake = preg_replace("/{$allowedSymbolsRegex}/", '', $value);

        if ($telPlus) {
            $phoneFake = '+' . $phoneFake;
        }

        $tel = $telFake;

        return $phoneFake;
    }

    public function parsePhoneNonFake($value, ?string &$tel = null, ?string &$telDigits = null, ?string &$telPlus = null) : string
    {
        $tel = null;

        $telNonFake = $this->parseTelNonFake($value, $telDigits, $telPlus);

        $allowedSymbolsRegex = ''
            . '[^'
            . '0-9'
            . preg_quote(' ()-', '/')
            . ']';

        $phoneNonFake = preg_replace("/{$allowedSymbolsRegex}/", '', $value);

        if ($telPlus) {
            $phoneNonFake = '+' . $phoneNonFake;
        }

        $tel = $telNonFake;

        return $phoneNonFake;
    }

    public function parsePhoneReal(
        $value, ?string $region = '',
        ?string &$regionDetected = null,
        ?string &$tel = null, ?string &$telDigits = null, ?string &$telPlus = null
    ) : string
    {
        $tel = null;

        $telNonFake = $this->parseTelNonFake(
            $value,
            $telDigits, $telPlus
        );

        $phoneNumberObject = $this->parsePhoneNumber(
            $telNonFake, $region,
            $regionDetected
        );

        $formatted = $this->formatInternational($phoneNumberObject);

        $tel = $telNonFake;

        return $formatted;
    }


    public function parseTel($value, ?string &$telDigits = null, ?string &$telPlus = null) : string
    {
        $telDigits = null;
        $telPlus = null;

        if (is_a($value, '\libphonenumber\PhoneNumber')) {
            $tel = $this->formatE164($value);

            $isPlus = ($tel[ 0 ] === '+');

            $telDigitsString = $isPlus ? substr($tel, 1) : $tel;
            $telPlusString = $isPlus ? '+' : '';

        } else {
            if (! Lib::type()->string_not_empty($phone, $value)) {
                throw new LogicException(
                    [ 'The `value` should be non-empty string' ]
                );
            }

            $tel = preg_replace('/[^0-9]/', '', $phone);

            if ('' === $tel) {
                throw new LogicException(
                    [ 'The `tel` should be valid phone number', $value ]
                );
            }

            if (strlen($tel) > 15) {
                throw new RuntimeException(
                    [ 'The `tel` length should be less than 15 (16 - plus sign) according E164', $value ]
                );
            }

            $isPlus = ($phone[ 0 ] === '+');

            $telPlusString = $isPlus ? '+' : '';
            $telDigitsString = $tel;
        }

        $telDigits = $telDigitsString;
        $telPlus = $telPlusString;

        $tel = $isPlus
            ? '+' . $telDigitsString
            : $telDigitsString;

        return $tel;
    }

    public function parseTelFake($value, ?string &$telDigits = null, ?string &$telPlus = null) : string
    {
        $telString = $this->parseTel($value, $telDigits, $telPlus);

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
                    $dt = \DateTime::createFromFormat('YmdHis', $telDigits);

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
                    'The `value` must be fake phone number',
                    $value,
                ]
            );
        }

        return $telString;
    }

    public function parseTelNonFake($value, ?string &$telDigits = null, ?string &$telPlus = null) : string
    {
        $telString = $this->parseTel($value, $telDigits, $telPlus);

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
                            'The `value` must be not date-like',
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
        ?string &$regionDetected = null,
        ?string &$telDigits = null, ?string &$telPlus = null
    ) : string
    {
        $telNonFake = $this->parseTelNonFake(
            $value,
            $telDigits, $telPlus
        );

        $phoneNumberObject = $this->parsePhoneNumber(
            $telNonFake, $region,
            $regionDetected
        );

        $formatted = $this->formatE164($phoneNumberObject);

        return $formatted;
    }


    /**
     * @return object|\libphonenumber\PhoneNumber
     */
    public function parsePhoneNumber(
        $value, ?string $region = '',
        ?string &$regionDetected = null
    ) : object
    {
        $regionDetected = null;

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

        $regionDetected = $regionString;

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
        $timezoneWildcardsList = Lib::php()->to_list($timezoneWildcards);

        $phoneNumberObject = $this->parsePhoneNumber(
            $phoneNumber, $region
        );

        $phoneNumberToTimeZonesMapper = $this->getGiggseyPhoneNumberToTimeZonesMapper();

        $timezones = $phoneNumberToTimeZonesMapper
            ->getTimeZonesForNumber($phoneNumberObject)
        ;

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
