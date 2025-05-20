<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Modules\Social\EmailParser\DefaultEmailParser;
use Gzhegow\Lib\Modules\Social\EmailParser\EmailParserInterface;
use Gzhegow\Lib\Modules\Social\PhoneManager\DefaultPhoneManager;
use Gzhegow\Lib\Modules\Social\PhoneManager\PhoneManagerInterface;


class SocialModule
{
    /**
     * @var EmailParserInterface
     */
    protected $emailParser;
    /**
     * @var PhoneManagerInterface
     */
    protected $phoneManager;


    public function newEmailParser() : EmailParserInterface
    {
        return new DefaultEmailParser();
    }

    public function cloneEmailParser() : EmailParserInterface
    {
        return clone $this->emailParser();
    }

    public function emailParser(?EmailParserInterface $emailParser = null) : EmailParserInterface
    {
        return $this->emailParser = null
            ?? $emailParser
            ?? $this->emailParser
            ?? new DefaultEmailParser();
    }


    public function newPhoneManager() : PhoneManagerInterface
    {
        return new DefaultPhoneManager(null);
    }

    public function clonePhoneManager() : PhoneManagerInterface
    {
        return clone $this->phoneManager();
    }

    public function phoneManager(?PhoneManagerInterface $phoneManager = null) : PhoneManagerInterface
    {
        return $this->phoneManager = null
            ?? $phoneManager
            ?? $this->phoneManager
            ?? new DefaultPhoneManager(null);
    }


    /**
     * @param string|null $result
     */
    public function type_email(
        &$result, $value, ?array $filters = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withEmailDomain = array_key_exists(0, $refs);
        if ($withEmailDomain) {
            $refEmailDomain =& $refs[ 0 ];
        }
        $refEmailDomain = null;

        $withEmailName = array_key_exists(1, $refs);
        if ($withEmailName) {
            $refEmailName =& $refs[ 1 ];
        }
        $refEmailName = null;

        try {
            $emailParser = $this->emailParser();

            $email = $emailParser->parseEmail(
                $value, $filters,
                $emailDomain, $emailName
            );

            if ($withEmailDomain) {
                $refEmailDomain = $emailDomain;
            }
            if ($withEmailName) {
                $refEmailName = $emailName;
            }
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refEmailDomain);
        unset($refEmailName);

        $result = $email;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_email_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withEmailDomain = array_key_exists(0, $refs);
        if ($withEmailDomain) {
            $refEmailDomain =& $refs[ 0 ];
        }
        $refEmailDomain = null;

        $withEmailName = array_key_exists(1, $refs);
        if ($withEmailName) {
            $refEmailName =& $refs[ 1 ];
        }
        $refEmailName = null;

        try {
            $emailParser = $this->emailParser();

            $email = $emailParser->parseEmailFake(
                $value,
                $emailDomain, $emailName
            );

            if ($withEmailDomain) {
                $refEmailDomain = $emailDomain;
            }
            if ($withEmailName) {
                $refEmailName = $emailName;
            }
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refEmailDomain);
        unset($refEmailName);

        $result = $email;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_email_non_fake(
        &$result, $value, ?array $filters = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withEmailDomain = array_key_exists(0, $refs);
        if ($withEmailDomain) {
            $refEmailDomain =& $refs[ 0 ];
        }
        $refEmailName = null;

        $withEmailName = array_key_exists(1, $refs);
        if ($withEmailName) {
            $refEmailName =& $refs[ 1 ];
        }
        $refEmailDomain = null;

        try {
            $emailParser = $this->emailParser();

            $email = $emailParser->parseEmailNonFake(
                $value, $filters,
                $emailDomain, $emailName
            );

            if ($withEmailDomain) {
                $refEmailDomain = $emailDomain;
            }
            if ($withEmailName) {
                $refEmailName = $emailName;
            }
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refEmailDomain);
        unset($refEmailName);

        $result = $email;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function type_phone(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withTel = array_key_exists(0, $refs);
        if ($withTel) {
            $refTel =& $refs[ 0 ];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 1 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 2 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $phone = $phoneManager->parsePhone(
                $value,
                $refTel, $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refTel);
        unset($refTelDigits);
        unset($refTelPlus);

        $result = $phone;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_phone_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withTel = array_key_exists(0, $refs);
        if ($withTel) {
            $refTel =& $refs[ 0 ];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 1 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 2 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $phone = $phoneManager->parsePhoneFake(
                $value,
                $refTel, $refTelDigits, $refTelPlus
            );
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refTel);
        unset($refTelDigits);
        unset($refTelPlus);

        $result = $phone;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_phone_non_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withTel = array_key_exists(0, $refs);
        if ($withTel) {
            $refTel =& $refs[ 0 ];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 1 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 2 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $phone = $phoneManager->parsePhoneNonFake(
                $value,
                $refTel, $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refTel);
        unset($refTelDigits);
        unset($refTelPlus);

        $result = $phone;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_phone_real(
        &$result, $value, ?string $region = '',
        array $refs = []
    ) : bool
    {
        $result = null;

        $withRegionDetected = array_key_exists(0, $refs);
        if ($withRegionDetected) {
            $refRegionDetected =& $refs[ 0 ];
        }
        $refRegionDetected = null;

        $withTel = array_key_exists(1, $refs);
        if ($withTel) {
            $refTel =& $refs[ 1 ];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(2, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 2 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(3, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 3 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $phone = $phoneManager->parsePhoneReal(
                $value, $region,
                $refRegionDetected,
                $refTel, $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refRegionDetected);
        unset($refTel);
        unset($refTelDigits);
        unset($refTelPlus);

        $result = $phone;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function type_tel(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withTelDigits = array_key_exists(0, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 0 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(1, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 1 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $tel = $phoneManager->parseTel(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refTelDigits);
        unset($refTelPlus);

        $result = $tel;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_tel_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withTelDigits = array_key_exists(0, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 0 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(1, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 1 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $tel = $phoneManager->parseTelFake(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refTelDigits);
        unset($refTelPlus);

        $result = $tel;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_tel_non_fake(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withTelDigits = array_key_exists(0, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 0 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(1, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 1 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $tel = $phoneManager->parseTelNonFake(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refTelDigits);
        unset($refTelPlus);

        $result = $tel;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_tel_real(
        &$result, $value, ?string $region = '',
        array $refs = []
    ) : bool
    {
        $result = null;

        $withRegionDetected = array_key_exists(0, $refs);
        if ($withRegionDetected) {
            $refRegionDetected =& $refs[ 0 ];
        }
        $refRegionDetected = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ($withTelDigits) {
            $refTelDigits =& $refs[ 1 ];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ($withTelPlus) {
            $refTelPlus =& $refs[ 2 ];
        }
        $refTelPlus = null;

        try {
            $phoneManager = $this->phoneManager();

            $tel = $phoneManager->parseTelReal(
                $value, $region,
                $refRegionDetected,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return false;
        }

        unset($refRegionDetected);
        unset($refTelDigits);
        unset($refTelPlus);

        $result = $tel;

        return true;
    }
}
