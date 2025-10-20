<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Modules\Social\EmailParser\DefaultEmailParser;
use Gzhegow\Lib\Modules\Social\EmailParser\EmailParserInterface;
use Gzhegow\Lib\Modules\Social\PhoneManager\DefaultPhoneManager;
use Gzhegow\Lib\Modules\Social\PhoneManager\PhoneManagerInterface;
use Gzhegow\Lib\Modules\Social\PhoneRegionDetector\PassPhoneRegionDetector;
use Gzhegow\Lib\Modules\Social\PhoneRegionDetector\PhoneRegionDetectorInterface;


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


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function newEmailParser() : EmailParserInterface
    {
        $instance = new DefaultEmailParser();

        return $instance;
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
            ?? $this->newEmailParser();
    }


    public function newPhoneManager(?PhoneRegionDetectorInterface $theRegionDetector = null) : PhoneManagerInterface
    {
        $theRegionDetector = $theRegionDetector ?? new PassPhoneRegionDetector();

        $instance = new DefaultPhoneManager($theRegionDetector);

        return $instance;
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
            ?? $this->newPhoneManager();
    }


    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_email(
        $value,
        ?array $filters = null,
        array $refs = []
    )
    {
        $withEmailDomain = array_key_exists(0, $refs);
        if ( $withEmailDomain ) {
            $refEmailDomain =& $refs[0];
        }
        $refEmailDomain = null;

        $withEmailName = array_key_exists(1, $refs);
        if ( $withEmailName ) {
            $refEmailName =& $refs[1];
        }
        $refEmailName = null;

        $theEmailParser = $this->emailParser();

        try {
            $email = $theEmailParser->parseEmail(
                $value, $filters,
                $emailDomain, $emailName
            );

            if ( $withEmailDomain ) {
                $refEmailDomain = $emailDomain;
            }
            if ( $withEmailName ) {
                $refEmailName = $emailName;
            }
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid email, that passes filter checks', $value, $filters ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($email);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_email_fake(
        $value,
        array $refs = []
    )
    {
        $withEmailDomain = array_key_exists(0, $refs);
        if ( $withEmailDomain ) {
            $refEmailDomain =& $refs[0];
        }
        $refEmailDomain = null;

        $withEmailName = array_key_exists(1, $refs);
        if ( $withEmailName ) {
            $refEmailName =& $refs[1];
        }
        $refEmailName = null;

        $theEmailParser = $this->emailParser();

        try {
            $email = $theEmailParser->parseEmailFake(
                $value,
                $emailDomain, $emailName
            );

            if ( $withEmailDomain ) {
                $refEmailDomain = $emailDomain;
            }
            if ( $withEmailName ) {
                $refEmailName = $emailName;
            }
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid email (fake), that passes filter checks', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($email);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_email_non_fake(
        $value,
        ?array $filters = null,
        array $refs = []
    )
    {
        $withEmailDomain = array_key_exists(0, $refs);
        if ( $withEmailDomain ) {
            $refEmailDomain =& $refs[0];
        }
        $refEmailDomain = null;

        $withEmailName = array_key_exists(1, $refs);
        if ( $withEmailName ) {
            $refEmailName =& $refs[1];
        }
        $refEmailDomain = null;

        $theEmailParser = $this->emailParser();

        try {
            $email = $theEmailParser->parseEmailNonFake(
                $value, $filters,
                $emailDomain, $emailName
            );

            if ( $withEmailDomain ) {
                $refEmailDomain = $emailDomain;
            }
            if ( $withEmailName ) {
                $refEmailName = $emailName;
            }
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid email, that passes filter checks', $value, $filters ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($email);
    }


    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_phone(
        $value,
        array $refs = []
    )
    {
        $withTel = array_key_exists(0, $refs);
        if ( $withTel ) {
            $refTel =& $refs[0];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[1];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[2];
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
            return Ret::err(
                [ 'The `value` should be valid phone', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($phone);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_phone_fake(
        $value,
        array $refs = []
    )
    {
        $withTel = array_key_exists(0, $refs);
        if ( $withTel ) {
            $refTel =& $refs[0];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[1];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[2];
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
            return Ret::err(
                [ 'The `value` should be valid phone (fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($phone);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_phone_non_fake(
        $value,
        array $refs = []
    )
    {
        $withTel = array_key_exists(0, $refs);
        if ( $withTel ) {
            $refTel =& $refs[0];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[1];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[2];
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
            return Ret::err(
                [ 'The `value` should be valid phone (non-fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($phone);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string, 3?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_phone_real(
        $value,
        ?string $region = '',
        array $refs = []
    )
    {
        $withRegionDetected = array_key_exists(0, $refs);
        if ( $withRegionDetected ) {
            $refRegionDetected =& $refs[0];
        }
        $refRegionDetected = null;

        $withTel = array_key_exists(1, $refs);
        if ( $withTel ) {
            $refTel =& $refs[1];
        }
        $refTel = null;

        $withTelDigits = array_key_exists(2, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[2];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(3, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[3];
        }
        $refTelPlus = null;

        $thePhoneManager = $this->phoneManager();

        try {
            $phone = $thePhoneManager->parsePhoneReal(
                $value, $region,
                $refRegionDetected,
                $refTel, $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid email, that match passed region', $value, $region ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($phone);
    }


    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_tel(
        $value,
        array $refs = []
    )
    {
        $withTelDigits = array_key_exists(0, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[0];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(1, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[1];
        }
        $refTelPlus = null;

        $thePhoneManager = $this->phoneManager();

        try {
            $tel = $thePhoneManager->parseTel(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid tel', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($tel);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_tel_fake(
        $value,
        array $refs = []
    )
    {
        $withTelDigits = array_key_exists(0, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[0];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(1, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[1];
        }
        $refTelPlus = null;

        $thePhoneManager = $this->phoneManager();

        try {
            $tel = $thePhoneManager->parseTelFake(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid tel (fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($tel);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_tel_non_fake(
        $value,
        array $refs = []
    )
    {
        $withTelDigits = array_key_exists(0, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[0];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(1, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[1];
        }
        $refTelPlus = null;

        $thePhoneManager = $this->phoneManager();

        try {
            $tel = $thePhoneManager->parseTelNonFake(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` should be valid tel (non-fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($tel);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function type_tel_real(
        $value,
        ?string $region = '',
        array $refs = []
    )
    {
        $withRegionDetected = array_key_exists(0, $refs);
        if ( $withRegionDetected ) {
            $refRegionDetected =& $refs[0];
        }
        $refRegionDetected = null;

        $withTelDigits = array_key_exists(1, $refs);
        if ( $withTelDigits ) {
            $refTelDigits =& $refs[1];
        }
        $refTelDigits = null;

        $withTelPlus = array_key_exists(2, $refs);
        if ( $withTelPlus ) {
            $refTelPlus =& $refs[2];
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
            return Ret::err(
                [ 'The `value` should be valid tel, that match passed region', $value, $region ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($tel);
    }
}
