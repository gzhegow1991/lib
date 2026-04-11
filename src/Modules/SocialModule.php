<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\Runtime\ComposerException;
use Gzhegow\Lib\Modules\Social\EmailParser\DefaultEmailParser;
use Gzhegow\Lib\Modules\Social\EmailParser\EmailParserInterface;
use Gzhegow\Lib\Modules\Social\PhoneManager\DefaultPhoneManager;
use Gzhegow\Lib\Modules\Social\PhoneManager\PhoneManagerInterface;
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


    public function newPhoneManager(
        ?PhoneRegionDetectorInterface $theRegionDetector = null
    ) : PhoneManagerInterface
    {
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
     * @return Ret<string>|string
     */
    public function type_email($fb,
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

        try {
            $theEmailParser = $this->emailParser();

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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid email, that passes filter checks', $value, $filters ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $email);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_email_fake($fb,
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

        try {
            $theEmailParser = $this->emailParser();

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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid email (fake), that passes filter checks', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $email);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_email_non_fake($fb,
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

        try {
            $theEmailParser = $this->emailParser();

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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid email, that passes filter checks', $value, $filters ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $email);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_email_maybe_fake($fb,
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

        try {
            $theEmailParser = $this->emailParser();

            $email = $theEmailParser->parseEmailMaybeFake(
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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid email, that passes filter checks', $value, $filters ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $email);
    }


    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_phone($fb,
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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid phone', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $phone);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_phone_fake($fb,
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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid phone (fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $phone);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_phone_non_fake($fb,
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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid phone (non-fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $phone);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_phone_maybe_fake($fb,
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

            $phone = $phoneManager->parsePhoneMaybeFake(
                $value,
                $refTel, $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid phone', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $phone);
    }

    /**
     * > использует библиотеку libphonenumber, чтобы убедится, что телефон соответствует стандартам Google
     *
     * @param array{ 0?: string, 1?: string, 2?: string, 3?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_phone_real($fb,
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

        try {
            $thePhoneManager = $this->phoneManager();

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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid email, that match passed region', $value, $region ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $phone);
    }


    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_tel($fb,
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

        try {
            $thePhoneManager = $this->phoneManager();

            $tel = $thePhoneManager->parseTel(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid tel', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $tel);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_tel_fake($fb,
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

        try {
            $thePhoneManager = $this->phoneManager();

            $tel = $thePhoneManager->parseTelFake(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid tel (fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $tel);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_tel_non_fake($fb,
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

        try {
            $thePhoneManager = $this->phoneManager();

            $tel = $thePhoneManager->parseTel(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid tel (non-fake)', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $tel);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_tel_maybe_fake($fb,
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

        try {
            $thePhoneManager = $this->phoneManager();

            $tel = $thePhoneManager->parseTelMaybeFake(
                $value,
                $refTelDigits, $refTelPlus
            );
        }
        catch ( ComposerException $e ) {
            throw $e;
        }
        catch ( \Throwable $e ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid tel', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $tel);
    }

    /**
     * > использует библиотеку libphonenumber, чтобы убедится, что телефон соответствует стандартам Google
     *
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function type_tel_real($fb,
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
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid tel, that match passed region', $value, $region ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $tel);
    }
}
