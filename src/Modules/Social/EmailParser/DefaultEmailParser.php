<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUndefinedClassInspection
 * @noinspection PhpUndefinedNamespaceInspection
 */

namespace Gzhegow\Lib\Modules\Social\EmailParser;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\ComposerException;


class DefaultEmailParser implements EmailParserInterface
{
    const EGULIAS_EMAIL_VALIDATOR = '\Egulias\EmailValidator\EmailValidator';


    /**
     * @var string[]
     */
    protected $emailFakeRegexIndex = [
        '/^no-reply@/'    => true,
        '/@example.com$/' => true,
    ];


    /**
     * @return \Egulias\EmailValidator\EmailValidator
     */
    protected function newEguliasEmailValidator() : object
    {
        $commands = [
            'composer require egulias/email-validator',
        ];

        if (! class_exists($eguliasEmailValidatorClass = static::EGULIAS_EMAIL_VALIDATOR)) {
            throw new ComposerException([
                ''
                . 'Please, run following commands: '
                . '[ ' . implode(' ][ ', $commands) . ' ]',
            ]);
        }

        return new $eguliasEmailValidatorClass();
    }


    /**
     * @return static
     */
    public function setEmailFakeRegexes(?array $regexList)
    {
        if (null === $regexList) {
            $this->emailFakeRegexIndex = [
                '/^no-reply@/'    => true,
                '/@example.com$/' => true,
            ];

        } else {
            $this->emailFakeRegexIndex = [];

            $this->addEmailFakeRegexes($regexList);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function addEmailFakeRegexes(array $regexList)
    {
        if ([] === $regexList) {
            return $this;
        }

        $theType = Lib::$type;

        foreach ( $regexList as $regex ) {
            $regexValid = $theType->regex($regex)->orThrow();

            if (! isset($this->emailFakeRegexIndex[ $regexValid ])) {
                $this->emailFakeRegexIndex[ $regexValid ] = true;
            }
        }

        return $this;
    }


    public function parseEmail(
        $value, ?array $filters = null,
        ?string &$refEmailDomain = null, ?string &$refEmailName = null
    ) : string
    {
        $filters = $filters ?? [ 'filter' => true ];

        [
            $emailString,
            $refEmailDomain,
            $refEmailName,
        ] = $this->parseEmailDomain($value);

        $this->parseEmailFilters(
            $emailString, $refEmailDomain, $refEmailName,
            $filters
        );

        return $emailString;
    }

    public function parseEmailFake(
        $value,
        ?string &$refEmailDomain = null, ?string &$refEmailName = null
    ) : string
    {
        [
            $emailString,
            $refEmailDomain,
            $refEmailName,
        ] = $this->parseEmailDomain($value);

        if (false === filter_var($emailString, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                [
                    'The `email` must pass `filter_var` check',
                    $value,
                ]
            );
        }

        $isFake = false;
        foreach ( $this->emailFakeRegexIndex as $regexp => $bool ) {
            if (preg_match($regexp, $emailString)) {
                $isFake = true;

                break;
            }
        }

        if (! $isFake) {
            throw new RuntimeException(
                [
                    'The `email` must match at least one of `emailFakeRegexIndex` items',
                    $value,
                ]
            );
        }

        return $emailString;
    }

    public function parseEmailNonFake(
        $value, ?array $filters = null,
        ?string &$refEmailDomain = null, ?string &$refEmailName = null
    ) : string
    {
        $filters = $filters ?? [ 'filter' => true ];

        [
            $emailString,
            $refEmailDomain,
            $refEmailName,
        ] = $this->parseEmailDomain($value);

        foreach ( $this->emailFakeRegexIndex as $regexp => $bool ) {
            if (preg_match($regexp, $emailString)) {
                throw new RuntimeException(
                    [
                        'The `email` must not match any of `emailFakeRegexIndex` items',
                        $value,
                    ]
                );
            }
        }

        $this->parseEmailFilters(
            $emailString, $refEmailDomain, $refEmailName,
            $filters
        );

        return $emailString;
    }


    protected function parseEmailDomain(string $email) : array
    {
        $theType = Lib::$type;

        $emailStringNotEmpty = $theType->string_not_empty($email)->orThrow();

        [ $emailName, $emailDomain ] = explode('@', $emailStringNotEmpty, 2) + [ '', '' ];

        if ('' === $emailDomain) {
            throw new LogicException(
                [
                    'The `domain` should be a non-empty string',
                    $emailDomain,
                    $email,
                ]
            );
        }

        return [ $emailStringNotEmpty, $emailDomain, $emailName ];
    }

    protected function parseEmailFilters(
        string $email, string $emailDomain, string $emailName,
        array $filters
    ) : void
    {
        $theHttp = Lib::$http;

        $filtersKnownIndex = [
            'filter'         => true,
            'filter_unicode' => true,
            //
            'rfc'            => true,
            'rfc_non_strict' => true,
            'spoof'          => true,
            //
            'mx'             => true,
            'dns'            => true,
        ];

        $filtersIndex = [];
        foreach ( $filters as $filterKey => $filter ) {
            if (is_string($filterKey)) {
                $filter = $filterKey;
            }

            $filtersIndex[ $filter ] = true;
        }

        $filtersIntersectIndex = array_intersect_key($filtersIndex, $filtersKnownIndex);

        $eguliasEmailValidator = null;
        if (false
            || isset($filtersIntersectIndex[ 'rfc' ])
            || isset($filtersIntersectIndex[ 'rfc_non_strict' ])
            || isset($filtersIntersectIndex[ 'spoof' ])
            || isset($filtersIntersectIndex[ 'dns' ])
        ) {
            $eguliasEmailValidator = $this->newEguliasEmailValidator();
        }

        if ([] !== $filtersIntersectIndex) {
            $eguliasFilters = [];

            foreach ( $filtersIntersectIndex as $filter => $bool ) {
                if ('filter' === $filter) {
                    if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new RuntimeException(
                            [
                                'The `email` must pass `filter_var()` check',
                                $email,
                            ]
                        );
                    }

                } elseif ('filter_unicode' === $filter) {
                    $emailDomainAscii = false;

                    try {
                        $emailDomainAscii = $theHttp->idn_to_ascii($emailDomain);
                    }
                    catch ( \Throwable $e ) {
                    }

                    if (false === $emailDomainAscii) {
                        throw new RuntimeException(
                            [
                                'The `email` is unable to transform `emailDomain` to ASCII',
                                $emailDomain,
                                $email,
                            ]
                        );
                    }

                    $emailAscii = $emailName . '@' . $emailDomainAscii;

                    if (false === filter_var($emailAscii, FILTER_VALIDATE_EMAIL)) {
                        throw new RuntimeException(
                            [
                                'The `email` must pass `filter_var()` check',
                                $emailAscii,
                                $email,
                            ]
                        );
                    }

                } elseif ('mx' === $filter) {
                    $hasMxRecords = getmxrr($emailDomain, $mxHosts);

                    if (false === $hasMxRecords) {
                        throw new RuntimeException(
                            [
                                'The `email` must pass `getmxrr()` check',
                                $email,
                            ]
                        );
                    }

                } elseif ('rfc_non_strict' === $filter) {
                    $eguliasFilters[] = new \Egulias\EmailValidator\Validation\RFCValidation();

                } elseif ('rfc' === $filter) {
                    $eguliasFilters[] = new \Egulias\EmailValidator\Validation\NoRFCWarningsValidation();

                } elseif ('spoof' === $filter) {
                    $eguliasFilters[] = new \Egulias\EmailValidator\Validation\DNSCheckValidation();

                } elseif ('dns' === $filter) {
                    $eguliasFilters[] = new \Egulias\EmailValidator\Validation\DNSCheckValidation();
                }
            }

            if ([] !== $eguliasFilters) {
                $emailValidation = new \Egulias\EmailValidator\Validation\MultipleValidationWithAnd(
                    $eguliasFilters
                );

                $isValid = $eguliasEmailValidator->isValid(
                    $email,
                    $emailValidation
                );

                if (false === $isValid) {
                    throw new RuntimeException(
                        'The `email` must succesfully pass filters',
                        $filters,
                        $email,
                        $eguliasEmailValidator,
                    );
                }
            }
        }
    }
}
