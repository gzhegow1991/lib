<?php
/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Str\Slugger\DefaultSlugger;
use Gzhegow\Lib\Modules\Str\Slugger\SluggerInterface;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;
use Gzhegow\Lib\Modules\Str\Inflector\DefaultInflector;
use Gzhegow\Lib\Modules\Str\Inflector\InflectorInterface;
use Gzhegow\Lib\Modules\Str\Interpolator\DefaultInterpolator;
use Gzhegow\Lib\Modules\Str\Interpolator\InterpolatorInterface;


class StrModule
{
    /**
     * @var bool
     */
    protected static $mbstring;

    /**
     * @param int|false|null $mbstring
     */
    public static function staticMbstring($mbstring = null) : bool
    {
        $last = static::$mbstring;

        if ( null !== $mbstring ) {
            if ( false === $mbstring ) {
                static::$mbstring = extension_loaded('mbstring');

            } else {
                $mbstringBool = (bool) $mbstring;

                if ( $mbstringBool ) {
                    if ( ! extension_loaded('mbstring') ) {
                        throw new ExtensionException(
                            [ 'Missing PHP extension: mbstring' ]
                        );
                    }
                }

                static::$mbstring = $mbstringBool;
            }
        }

        static::$mbstring = static::$mbstring ?? extension_loaded('mbstring');

        return $last;
    }


    /**
     * @var InflectorInterface
     */
    protected $inflector;
    /**
     * @var InterpolatorInterface
     */
    protected $interpolator;
    /**
     * @var SluggerInterface
     */
    protected $slugger;

    /**
     * @var array<string, callable|callable-string>
     */
    protected $mbstringFuncMap = [];


    public function __construct()
    {
        static::$mbstring = static::$mbstring ?? extension_loaded('mbstring');

        if ( static::$mbstring ) {
            $theMb = Lib::mb();

            $this->mbstringFuncMap['lcfirst'] = [ $theMb, 'lcfirst' ];
            $this->mbstringFuncMap['ucfirst'] = [ $theMb, 'ucfirst' ];
            $this->mbstringFuncMap['lcwords'] = [ $theMb, 'lcwords' ];
            $this->mbstringFuncMap['ucwords'] = [ $theMb, 'ucwords' ];

            if ( PHP_VERSION_ID < 70400 ) {
                $this->mbstringFuncMap['str_split'] = [ $theMb, 'str_split' ];
            }
        }
    }

    public function __initialize()
    {
        return $this;
    }


    public function newInflector() : InflectorInterface
    {
        $instance = new DefaultInflector();

        return $instance;
    }

    public function cloneInflector() : InflectorInterface
    {
        return clone $this->inflector();
    }

    public function inflector(?InflectorInterface $inflector = null) : InflectorInterface
    {
        return $this->inflector = null
            ?? $inflector
            ?? $this->inflector
            ?? new DefaultInflector();
    }


    public function newInterpolator() : InterpolatorInterface
    {
        $instance = new DefaultInterpolator();

        return $instance;
    }

    public function cloneInterpolator() : InterpolatorInterface
    {
        return clone $this->interpolator();
    }

    public function interpolator(?InterpolatorInterface $interpolator = null) : InterpolatorInterface
    {
        return $this->interpolator = null
            ?? $interpolator
            ?? $this->interpolator
            ?? new DefaultInterpolator();
    }


    public function newSlugger() : SluggerInterface
    {
        $instance = new DefaultSlugger();

        return $instance;
    }

    public function cloneSlugger() : SluggerInterface
    {
        return clone $this->slugger();
    }

    public function slugger(?SluggerInterface $slugger = null) : SluggerInterface
    {
        return $this->slugger = null
            ?? $slugger
            ?? $this->slugger
            ?? new DefaultSlugger(null);
    }


    /**
     * @param string   $fnName
     * @param callable $fn
     *
     * @return static
     */
    public function mb_func_register(string $fnName, $fn)
    {
        if ( isset($this->mbstringFuncMap[$fnName]) ) {
            throw new LogicException(
                [ 'The `fnName` is already registered', $fnName ]
            );
        }

        $this->mbstringFuncMap[$fnName] = $fn;

        return $this;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable
     *
     * @noinspection PhpDocSignatureInspection
     */
    public function mb_func(string $fn)
    {
        if ( ! $this->staticMbstring() ) {
            return $fn;
        }

        $result = null
            ?? $this->mbstringFuncMap[$fn]
            ?? 'mb_' . $fn;

        return $result;
    }


    /**
     * @return Ret<string>
     */
    public function type_a_string($value)
    {
        if ( is_string($value) ) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be string', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_a_string_empty($value)
    {
        if ( '' === $value ) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be string, empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_a_string_not_empty($value)
    {
        if ( is_string($value) && ('' !== $value) ) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be string, non empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_a_trim($value, ?string $characters = null)
    {
        $characters = $characters ?? " \n\r\t\v\0";

        if ( ! is_string($value) ) {
            return Ret::err(
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $valueTrim = trim($value, $characters);

        if ( '' !== $valueTrim ) {
            return Ret::val($valueTrim);
        }

        return Ret::err(
            [ 'The `value` should be trim', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<string>
     */
    public function type_string($value)
    {
        if ( is_string($value) ) {
            return Ret::val($value);
        }

        if ( false
            || (null === $value)
            // || ('' === $value)
            || (is_bool($value))
            || (is_array($value))
            || (is_float($value) && (! is_finite($value)))
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            || (Nil::is($value))
        ) {
            // > NULL is equal EMPTY STRING but cannot be cast to
            // > BOOLEAN is not string
            // > NAN, INF, -INF is not string
            // > ARRAY is not string
            // > RESOURCE is not string
            // > CLOSED RESOURCE is not string
            // > NIL is not string

            return Ret::err(
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( is_object($value) ) {
            if ( ! method_exists($value, '__toString') ) {
                return Ret::err(
                    [ 'The `value` unable to be converted to string', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        try {
            $valueString = (string) $value;
        }
        catch ( \Throwable $e ) {
            return Ret::err(
                [ 'The `value` is unable to be converted to string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueString);
    }

    /**
     * @return Ret<string>
     */
    public function type_string_empty($value)
    {
        if ( ! $this->type_string($value)->isOk([ &$valueString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( '' === $valueString ) {
            return Ret::val('');
        }

        return Ret::err(
            [ 'The `value` should be empty string', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_string_not_empty($value)
    {
        if ( ! $this->type_string($value)->isOk([ &$valueString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( '' !== $valueString ) {
            return Ret::val($valueString);
        }

        return Ret::err(
            [ 'The `value` should be string, non empty', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_trim($value, ?string $characters = null)
    {
        $characters = $characters ?? " \n\r\t\v\0";

        if ( ! $this->type_string($value)->isOk([ &$valueString, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $valueString = trim($valueString, $characters);

        if ( '' !== $valueString ) {
            return Ret::val($valueString);
        }

        return Ret::err(
            [ 'The `value` should be trim', $value ],
            [ __FILE__, __LINE__ ]
        );
    }


    /**
     * @return Ret<string>
     */
    public function type_char($value)
    {
        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( 1 === strlen($valueStringNotEmpty) ) {
            return Ret::val($valueStringNotEmpty);
        }

        return Ret::err(
            [ 'The `value` should be char', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_letter($value)
    {
        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( 1 === $this->strlen($valueStringNotEmpty) ) {
            return Ret::val($valueStringNotEmpty);
        }

        return Ret::err(
            [ 'The `value` should be letter', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>
     */
    public function type_word($value)
    {
        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        preg_replace('/\s+/', '', $valueStringNotEmpty, 1, $count);
        if ( $count > 0 ) {
            return Ret::err(
                [ 'The `value` should not contain any whitespaces', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::err(
            [ 'The `value` should be word', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<Alphabet>
     */
    public function type_alphabet($value)
    {
        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        preg_replace('/\s+/', '', $valueStringNotEmpty, 1, $count);
        if ( $count > 0 ) {
            return Ret::err(
                [ 'The `value` should not contain any whitespaces', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $fnStrlen = $this->mb_func('strlen');

        $len = $fnStrlen($valueStringNotEmpty);
        if ( $len <= 1 ) {
            return Ret::err(
                [ 'The `value` should contain at least two letters', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $fnOrd = $this->mb_func('ord');
        $fnSubstr = $this->mb_func('substr');

        $seen = [];
        $regex = '/[';
        $regexNot = '/[^';
        for ( $i = 0; $i < $len; $i++ ) {
            $letter = $fnSubstr($valueStringNotEmpty, $i, 1);

            if ( isset($seen[$letter]) ) {
                return Ret::err(
                    [ 'The `value` should contain unique letters', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
            $seen[$letter] = true;

            $letterRegex = sprintf('\x{%X}', $fnOrd($letter));

            $regex .= $letterRegex;
            $regexNot .= $letterRegex;
        }
        $regex .= ']+/';
        $regexNot .= ']/';

        $alphabet = new Alphabet(
            $valueStringNotEmpty,
            $len,
            $regex,
            $regexNot
        );

        return Ret::val($alphabet);
    }


    /**
     * @return Ret<string>
     */
    public function type_ctype_digit($value)
    {
        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( extension_loaded('ctype') ) {
            if ( ctype_digit($valueStringNotEmpty) ) {
                return Ret::val($valueStringNotEmpty);
            }

            return Ret::err(
                [ 'The `value` should pass `ctype_digit` check', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( ! preg_match('~[^0-9]~', $valueStringNotEmpty) ) {
            return Ret::err(
                [ 'The `value` should contain only digits', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_ctype_alpha($value, ?bool $allowUpperCase = null)
    {
        $allowUpperCase = $allowUpperCase ?? true;

        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( extension_loaded('ctype') ) {
            if ( ! $allowUpperCase ) {
                if ( strtolower($valueStringNotEmpty) !== $valueStringNotEmpty ) {
                    return Ret::err(
                        [ 'The `value` should not contain upper case letters', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            if ( ctype_alpha($valueStringNotEmpty) ) {
                return Ret::val($valueStringNotEmpty);
            }

            return Ret::err(
                [ 'The `value` should pass `ctype_alpha` check', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $regexFlags = $allowUpperCase
            ? 'i'
            : '';

        if ( preg_match('~[^a-z]~' . $regexFlags, $valueStringNotEmpty) ) {
            return Ret::err(
                [ 'The `value` should contain only [a-z] letters', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_ctype_alnum($value, ?bool $allowUpperCase = null)
    {
        $allowUpperCase = $allowUpperCase ?? true;

        if ( ! $this->type_string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ]) ) {
            return Ret::err(
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( extension_loaded('ctype') ) {
            if ( ! $allowUpperCase ) {
                if ( strtolower($valueStringNotEmpty) !== $valueStringNotEmpty ) {
                    return Ret::err(
                        [ 'The `value` should not contain upper case letters', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            if ( ctype_alnum($valueStringNotEmpty) ) {
                return Ret::val($valueStringNotEmpty);
            }

            return Ret::err(
                [ 'The `value` should pass `ctype_alnum` check', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $regexFlags = $allowUpperCase
            ? 'i'
            : '';

        if ( preg_match('~[^0-9a-z]~' . $regexFlags, $valueStringNotEmpty) ) {
            return Ret::err(
                [ 'The `value` should contain only [a-z0-9] letters', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueStringNotEmpty);
    }


    public function loadAsciiControls(?bool $hex = null) : array
    {
        $hex = $hex ?? false;

        if ( $hex ) {
            $list = [
                chr(0)  => '\x00', // "\0"   // NULL (ASCII 0)
                chr(1)  => '\x01', // "\x01" // SOH (Start of Heading) (ASCII 1)
                chr(2)  => '\x02', // "\x02" // STX (Start of Text)   (ASCII 2)
                chr(3)  => '\x03', // "\x03" // ETX (End of Text)     (ASCII 3)
                chr(4)  => '\x04', // "\x04" // EOT (End of Transmission) (ASCII 4)
                chr(5)  => '\x05', // "\x05" // ENQ (Enquiry)         (ASCII 5)
                chr(6)  => '\x06', // "\x06" // ACK (Acknowledge)     (ASCII 6)
                chr(7)  => '\x07', // "\a" // BEL (Bell)            (ASCII 7)
                chr(8)  => '\x08', // "\b" // BS  (Backspace)       (ASCII 8)
                chr(9)  => '\x09', // "\t" // TAB (Horizontal Tab)  (ASCII 9)
                chr(10) => '\x0A', // "\n" // LF  (Line Feed)       (ASCII 10)
                chr(11) => '\x0B', // "\v" // VT  (Vertical Tab)    (ASCII 11)
                chr(12) => '\x0C', // "\f" // FF  (Form Feed)       (ASCII 12)
                chr(13) => '\x0D', // "\r" // CR  (Carriage Return) (ASCII 13)
                chr(14) => '\x0E', // "\x0E" // SO  (Shift Out)       (ASCII 14)
                chr(15) => '\x0F', // "\x0F" // SI  (Shift In)        (ASCII 15)
                chr(16) => '\x10', // "\x10" // DLE (Data Link Escape)(ASCII 16)
                chr(17) => '\x11', // "\x11" // DC1 (Device Control 1)(ASCII 17)
                chr(18) => '\x12', // "\x12" // DC2 (Device Control 2)(ASCII 18)
                chr(19) => '\x13', // "\x13" // DC3 (Device Control 3)(ASCII 19)
                chr(20) => '\x14', // "\x14" // DC4 (Device Control 4)(ASCII 20)
                chr(21) => '\x15', // "\x15" // NAK (Negative Acknowledge) (ASCII 21)
                chr(22) => '\x16', // "\x16" // SYN (Synchronous Idle) (ASCII 22)
                chr(23) => '\x17', // "\x17" // ETB (End of Block)    (ASCII 23)
                chr(24) => '\x18', // "\x18" // CAN (Cancel)           (ASCII 24)
                chr(25) => '\x19', // "\x19" // EM  (End of Medium)    (ASCII 25)
                chr(26) => '\x1A', // "\x1A" // SUB (Substitute)       (ASCII 26)
                chr(27) => '\x1B', // "\e" // ESC (Escape)           (ASCII 27)
                chr(28) => '\x1C', // "\x1C" // FS  (File Separator)   (ASCII 28)
                chr(29) => '\x1D', // "\x1D" // GS  (Group Separator)  (ASCII 29)
                chr(30) => '\x1E', // "\x1E" // RS  (Record Separator) (ASCII 30)
                chr(31) => '\x1F', // "\x1F" // US  (Unit Separator)   (ASCII 31)
            ];

        } else {
            $list = [
                chr(0)  => '\0',   // "\0"   // NULL (ASCII 0)
                chr(1)  => '\x01', // "\x01" // SOH (Start of Heading) (ASCII 1)
                chr(2)  => '\x02', // "\x02" // STX (Start of Text)   (ASCII 2)
                chr(3)  => '\x03', // "\x03" // ETX (End of Text)     (ASCII 3)
                chr(4)  => '\x04', // "\x04" // EOT (End of Transmission) (ASCII 4)
                chr(5)  => '\x05', // "\x05" // ENQ (Enquiry)         (ASCII 5)
                chr(6)  => '\x06', // "\x06" // ACK (Acknowledge)     (ASCII 6)
                chr(7)  => '\a',   // "\x07" // BEL (Bell)            (ASCII 7)
                chr(8)  => '\b',   // "\x08" // BS  (Backspace)       (ASCII 8)
                chr(9)  => '\t',   // "\x09" // TAB (Horizontal Tab)  (ASCII 9)
                chr(10) => '\n',   // "\x0A" // LF  (Line Feed)       (ASCII 10)
                chr(11) => '\v',   // "\x0B" // VT  (Vertical Tab)    (ASCII 11)
                chr(12) => '\f',   // "\x0C" // FF  (Form Feed)       (ASCII 12)
                chr(13) => '\r',   // "\x0D" // CR  (Carriage Return) (ASCII 13)
                chr(14) => '\x0E', // "\x0E" // SO  (Shift Out)       (ASCII 14)
                chr(15) => '\x0F', // "\x0F" // SI  (Shift In)        (ASCII 15)
                chr(16) => '\x10', // "\x10" // DLE (Data Link Escape)(ASCII 16)
                chr(17) => '\x11', // "\x11" // DC1 (Device Control 1)(ASCII 17)
                chr(18) => '\x12', // "\x12" // DC2 (Device Control 2)(ASCII 18)
                chr(19) => '\x13', // "\x13" // DC3 (Device Control 3)(ASCII 19)
                chr(20) => '\x14', // "\x14" // DC4 (Device Control 4)(ASCII 20)
                chr(21) => '\x15', // "\x15" // NAK (Negative Acknowledge) (ASCII 21)
                chr(22) => '\x16', // "\x16" // SYN (Synchronous Idle) (ASCII 22)
                chr(23) => '\x17', // "\x17" // ETB (End of Block)    (ASCII 23)
                chr(24) => '\x18', // "\x18" // CAN (Cancel)           (ASCII 24)
                chr(25) => '\x19', // "\x19" // EM  (End of Medium)    (ASCII 25)
                chr(26) => '\x1A', // "\x1A" // SUB (Substitute)       (ASCII 26)
                chr(27) => '\e',   // "\x1B" // ESC (Escape)           (ASCII 27)
                chr(28) => '\x1C', // "\x1C" // FS  (File Separator)   (ASCII 28)
                chr(29) => '\x1D', // "\x1D" // GS  (Group Separator)  (ASCII 29)
                chr(30) => '\x1E', // "\x1E" // RS  (Record Separator) (ASCII 30)
                chr(31) => '\x1F', // "\x1F" // US  (Unit Separator)   (ASCII 31)
            ];
        }

        return $list;
    }

    public function loadAsciiControlsNoTrims(?bool $hex = null) : array
    {
        $list = $this->loadAsciiControls($hex);

        unset($list[chr(9)]);
        unset($list[chr(10)]);
        unset($list[chr(11)]);
        unset($list[chr(13)]);

        return $list;
    }

    public function loadAsciiControlsOnlyTrims(?bool $hex = null) : array
    {
        $hex = $hex ?? false;

        if ( $hex ) {
            $list = [
                chr(9)  => '\x09', // "\t" // TAB (Horizontal Tab)  (ASCII 9)
                chr(10) => '\x0A', // "\n" // LF  (Line Feed)       (ASCII 10)
                chr(11) => '\x0B', // "\v" // VT  (Vertical Tab)    (ASCII 11)
                chr(13) => '\x0D', // "\r" // CR  (Carriage Return) (ASCII 13)
            ];

        } else {
            $list = [
                chr(9)  => '\t',   // "\x09" // TAB (Horizontal Tab)  (ASCII 9)
                chr(10) => '\n',   // "\x0A" // LF  (Line Feed)       (ASCII 10)
                chr(11) => '\v',   // "\x0B" // VT  (Vertical Tab)    (ASCII 11)
                chr(13) => '\r',   // "\x0D" // CR  (Carriage Return) (ASCII 13)
            ];
        }

        return $list;
    }


    public function loadAccents() : array
    {
        $list = [
            // "ɵ" => "-",
            // "ꞁ" => "-",
            // "ꞃ" => "-",
            // "ꞅ" => "-",
            //
            "ß" => "ss",
            "à" => "a",
            "á" => "a",
            "â" => "a",
            "ã" => "a",
            "ä" => "a",
            "å" => "a",
            "æ" => "ae",
            "ç" => "c",
            "è" => "e",
            "é" => "e",
            "ê" => "e",
            "ë" => "e",
            "ì" => "i",
            "í" => "i",
            "î" => "i",
            "ï" => "i",
            "ð" => "d",
            "ñ" => "n",
            "ò" => "o",
            "ó" => "o",
            "ô" => "o",
            "õ" => "o",
            "ö" => "o",
            "ø" => "o",
            "ù" => "u",
            "ú" => "u",
            "û" => "u",
            "ü" => "u",
            "ý" => "y",
            "ÿ" => "y",
            "ā" => "a",
            "ă" => "a",
            "ą" => "a",
            "ć" => "c",
            "ĉ" => "c",
            "ċ" => "c",
            "č" => "c",
            "ď" => "d",
            "đ" => "d",
            "ē" => "e",
            "ĕ" => "e",
            "ė" => "e",
            "ę" => "e",
            "ě" => "e",
            "ĝ" => "g",
            "ğ" => "g",
            "ġ" => "g",
            "ģ" => "g",
            "ĥ" => "h",
            "ħ" => "h",
            "ĩ" => "i",
            "ī" => "i",
            "ĭ" => "i",
            "į" => "i",
            "ĳ" => "ij",
            "ĵ" => "j",
            "ķ" => "k",
            "ĺ" => "l",
            "ļ" => "l",
            "ľ" => "l",
            "ŀ" => "l",
            "ł" => "l",
            "ń" => "n",
            "ņ" => "n",
            "ň" => "n",
            "ŋ" => "n",
            "ō" => "o",
            "ŏ" => "o",
            "ő" => "o",
            "œ" => "oe",
            "ŕ" => "r",
            "ŗ" => "r",
            "ř" => "r",
            "ś" => "s",
            "ŝ" => "s",
            "ş" => "s",
            "š" => "s",
            "ţ" => "t",
            "ť" => "t",
            "ŧ" => "t",
            "ũ" => "u",
            "ū" => "u",
            "ŭ" => "u",
            "ů" => "u",
            "ű" => "u",
            "ų" => "u",
            "ŵ" => "w",
            "ŷ" => "y",
            "ź" => "z",
            "ż" => "z",
            "ž" => "z",
            "ſ" => "s",
            "ƀ" => "b",
            "ƃ" => "b",
            "ƈ" => "c",
            "ƌ" => "d",
            "ƙ" => "k",
            "ƚ" => "l",
            "ơ" => "o",
            "ƫ" => "t",
            "ƭ" => "t",
            "ư" => "u",
            "ƴ" => "y",
            "ƶ" => "z",
            "ǆ" => "dz",
            "ǉ" => "lj",
            "ǌ" => "nj",
            "ǎ" => "a",
            "ǐ" => "i",
            "ǒ" => "o",
            "ǔ" => "u",
            "ǖ" => "u",
            "ǘ" => "u",
            "ǚ" => "u",
            "ǜ" => "u",
            "ǟ" => "a",
            "ǡ" => "a",
            "ǣ" => "ae",
            "ǧ" => "g",
            "ǩ" => "k",
            "ǫ" => "o",
            "ǭ" => "o",
            "ǳ" => "dz",
            "ǻ" => "a",
            "ǽ" => "ae",
            "ǿ" => "o",
            "ȁ" => "a",
            "ȃ" => "a",
            "ȅ" => "e",
            "ȇ" => "e",
            "ȉ" => "i",
            "ȋ" => "i",
            "ȍ" => "o",
            "ȏ" => "o",
            "ȑ" => "r",
            "ȓ" => "r",
            "ȕ" => "u",
            "ȗ" => "u",
            "ș" => "s",
            "ț" => "t",
            "ȟ" => "h",
            "ȧ" => "a",
            "ȩ" => "e",
            "ȫ" => "o",
            "ȭ" => "o",
            "ȯ" => "o",
            "ȱ" => "o",
            "ȳ" => "y",
            "ȴ" => "l",
            "ȵ" => "n",
            "ȶ" => "t",
            "ȷ" => "j",
            "ȼ" => "c",
            "ȿ" => "s",
            "ɇ" => "e",
            "ɍ" => "r",
            "ɏ" => "y",
            "ɓ" => "b",
            "ɖ" => "d",
            "ɗ" => "d",
            "ɠ" => "g",
            "ɨ" => "i",
            "ʈ" => "t",
            "ё" => "e",
            "є" => "e",
            "ї" => "i",
            "ў" => "u",
            "ӑ" => "a",
            "ӓ" => "a",
            "ӗ" => "e",
            "ḁ" => "a",
            "ḃ" => "b",
            "ḅ" => "b",
            "ḇ" => "b",
            "ḋ" => "d",
            "ḍ" => "d",
            "ḏ" => "d",
            "ḑ" => "d",
            "ḓ" => "d",
            "ḕ" => "e",
            "ḗ" => "e",
            "ḙ" => "e",
            "ḛ" => "e",
            "ḝ" => "e",
            "ḟ" => "f",
            "ḡ" => "g",
            "ḥ" => "h",
            "ḧ" => "h",
            "ḩ" => "h",
            "ḫ" => "h",
            "ḭ" => "i",
            "ḯ" => "i",
            "ḱ" => "k",
            "ḳ" => "k",
            "ḵ" => "k",
            "ḻ" => "l",
            "ḽ" => "l",
            "ḿ" => "m",
            "ṁ" => "m",
            "ṃ" => "m",
            "ṅ" => "n",
            "ṇ" => "n",
            "ṉ" => "n",
            "ṋ" => "n",
            "ṍ" => "o",
            "ṏ" => "o",
            "ṑ" => "o",
            "ṓ" => "o",
            "ṕ" => "p",
            "ṗ" => "p",
            "ṙ" => "r",
            "ṛ" => "r",
            "ṝ" => "r",
            "ṟ" => "r",
            "ṡ" => "s",
            "ṣ" => "s",
            "ṥ" => "s",
            "ṧ" => "s",
            "ṩ" => "s",
            "ṫ" => "t",
            "ṭ" => "t",
            "ṯ" => "t",
            "ṱ" => "t",
            "ṳ" => "u",
            "ṵ" => "u",
            "ṷ" => "u",
            "ṹ" => "u",
            "ṻ" => "u",
            "ṽ" => "v",
            "ṿ" => "v",
            "ẁ" => "w",
            "ẃ" => "w",
            "ẅ" => "w",
            "ẇ" => "w",
            "ẉ" => "w",
            "ẋ" => "x",
            "ẍ" => "x",
            "ẏ" => "y",
            "ẑ" => "z",
            "ẓ" => "z",
            "ẕ" => "z",
            "ẚ" => "a",
            "ẛ" => "s",
            "ạ" => "a",
            "ả" => "a",
            "ấ" => "a",
            "ầ" => "a",
            "ẩ" => "a",
            "ẫ" => "a",
            "ậ" => "a",
            "ắ" => "a",
            "ằ" => "a",
            "ẳ" => "a",
            "ẵ" => "a",
            "ặ" => "a",
            "ẹ" => "e",
            "ẻ" => "e",
            "ẽ" => "e",
            "ế" => "e",
            "ề" => "e",
            "ể" => "e",
            "ễ" => "e",
            "ệ" => "e",
            "ỉ" => "i",
            "ị" => "i",
            "ọ" => "o",
            "ỏ" => "o",
            "ố" => "o",
            "ồ" => "o",
            "ổ" => "o",
            "ỗ" => "o",
            "ộ" => "o",
            "ớ" => "o",
            "ờ" => "o",
            "ở" => "o",
            "ỡ" => "o",
            "ợ" => "o",
            "ụ" => "u",
            "ủ" => "u",
            "ứ" => "u",
            "ừ" => "u",
            "ử" => "u",
            "ữ" => "u",
            "ự" => "u",
            "ỳ" => "y",
            "ⱥ" => "a",
            "ꞇ" => "t",
        ];

        return $list;
    }

    public function loadInvisibles() : array
    {
        $theMb = Lib::mb();

        $list = [
            // mb_chr(0x0020, 'UTF-8') => '\u{0020}', // > \u{0020}	// Space // Обычный пробел (между словами).
            //
            mb_chr(0x00A0, 'UTF-8') => '\u{00A0}', // > \u{00A0} // No-Break Space (NBSP) // Неразрывный пробел, предотвращает перенос строки.
            mb_chr(0x2000, 'UTF-8') => '\u{2000}', // > \u{2000} // En Quad // Пробел шириной с букву "N".
            mb_chr(0x2001, 'UTF-8') => '\u{2001}', // > \u{2001} // Em Quad // Пробел шириной с букву "M".
            mb_chr(0x2002, 'UTF-8') => '\u{2002}', // > \u{2002} // En Space // Половина ширины Em-пробела.
            mb_chr(0x2003, 'UTF-8') => '\u{2003}', // > \u{2003} // Em Space // Ширина примерно как буква "M".
            mb_chr(0x2004, 'UTF-8') => '\u{2004}', // > \u{2004} // Three-Per-Em Space // Треть от Em-пробела.
            mb_chr(0x2005, 'UTF-8') => '\u{2005}', // > \u{2005} // Four-Per-Em Space // Четверть от Em-пробела.
            mb_chr(0x2006, 'UTF-8') => '\u{2006}', // > \u{2006} // Six-Per-Em Space // Одна шестая Em-пробела.
            mb_chr(0x2007, 'UTF-8') => '\u{2007}', // > \u{2007} // Figure Space // Ширина цифры в шрифте с фиксированной шириной.
            mb_chr(0x2008, 'UTF-8') => '\u{2008}', // > \u{2008} // Punctuation Space // Ширина типографского знака препинания.
            mb_chr(0x2009, 'UTF-8') => '\u{2009}', // > \u{2009} // Thin Space // Узкий пробел.
            mb_chr(0x200A, 'UTF-8') => '\u{200A}', // > \u{200A} // Hair Space // Ещё более узкий пробел.
            mb_chr(0x200B, 'UTF-8') => '\u{200B}', // > \u{200B} // Zero Width Space // Невидимый пробел (нулевая ширина).
            mb_chr(0x200C, 'UTF-8') => '\u{200C}', // > \u{200C} // Zero Width Non-Joiner (ZWNJ) // Запрещает лигатуры между буквами.
            mb_chr(0x200D, 'UTF-8') => '\u{200D}', // > \u{200D} // Zero Width Joiner (ZWJ) // Объединяет символы, создавая лигатуры.
            mb_chr(0x200E, 'UTF-8') => '\u{200E}', // > \u{200E} // Left-to-Right Mark (LRM) // Управляет направлением текста (слева направо).
            mb_chr(0x200F, 'UTF-8') => '\u{200F}', // > \u{200F} // Right-to-Left Mark (RLM) // Управляет направлением текста (справа налево).
            mb_chr(0x202F, 'UTF-8') => '\u{202F}', // > \u{202F} // Narrow No-Break Space // Узкий неразрывный пробел.
            mb_chr(0x205F, 'UTF-8') => '\u{205F}', // > \u{205F} // Medium Mathematical Space // Средний математический пробел.
            mb_chr(0x2060, 'UTF-8') => '\u{2060}', // > \u{2060} // Word Joiner (WJ) // Запрещает разрывы слов, аналог NBSP, но нулевой ширины.
            mb_chr(0x3000, 'UTF-8') => '\u{3000}', // > \u{3000} // Ideographic Space // Широкий пробел в китайском/японском тексте.
            mb_chr(0xFEFF, 'UTF-8') => '\u{FEFF}', // > \u{FEFF} // Byte Order Mark (BOM) // Метка порядка байтов, часто используется для UTF-8.
            mb_chr(0x2800, 'UTF-8') => '\u{2800}', // > \u{2800} // Braille Pattern Blank // Пробел в системе Брайля.
            mb_chr(0x3164, 'UTF-8') => '\u{3164}', // > \u{3164} // Hangul Filler // Невидимый символ в корейском языке.
        ];

        return $list;
    }

    public function loadVowels() : array
    {
        $list = [
            'a' => [
                'a' => true,
                'à' => true,
                'á' => true,
                'â' => true,
                'ã' => true,
                'ä' => true,
                'å' => true,
                'æ' => true,
                'ā' => true,
                'ă' => true,
                'ą' => true,
                'ǎ' => true,
                'ǟ' => true,
                'ǡ' => true,
                'ǣ' => true,
                'ǻ' => true,
                'ǽ' => true,
                'ȁ' => true,
                'ȧ' => true,
                'ɐ' => true,
                'α' => true,
                'а' => true,
                'я' => true,
                'ӑ' => true,
                'ӓ' => true,
                'ḁ' => true,
                'ẚ' => true,
                'ạ' => true,
                'ả' => true,
                'ấ' => true,
                'ầ' => true,
                'ẩ' => true,
                'ẫ' => true,
                'ậ' => true,
                'ắ' => true,
                'ằ' => true,
                'ẳ' => true,
                'ẵ' => true,
                'ặ' => true,
            ],

            'e' => [
                'e' => true,
                'è' => true,
                'é' => true,
                'ê' => true,
                'ë' => true,
                'ē' => true,
                'ĕ' => true,
                'ė' => true,
                'ę' => true,
                'ě' => true,
                'ȅ' => true,
                'ȇ' => true,
                'ȩ' => true,
                'ɛ' => true,
                'ε' => true,
                'е' => true,
                'ё' => true,
                'є' => true,
                'ḕ' => true,
                'ḗ' => true,
                'ḙ' => true,
                'ḛ' => true,
                'ḝ' => true,
                'ẹ' => true,
                'ẻ' => true,
                'ẽ' => true,
                'ế' => true,
                'ề' => true,
                'ể' => true,
                'ễ' => true,
                'ệ' => true,
            ],

            'i' => [
                'i' => true,
                'ì' => true,
                'í' => true,
                'î' => true,
                'ï' => true,
                'ĩ' => true,
                'ī' => true,
                'ĭ' => true,
                'į' => true,
                'ǐ' => true,
                'ȉ' => true,
                'ȋ' => true,
                'ɪ' => true,
                'η' => true,
                'и' => true,
                'ы' => true,
                'і' => true,
                'ї' => true,
                'ḭ' => true,
                'ḯ' => true,
                'ỉ' => true,
                'ị' => true,
            ],

            'o' => [
                'o' => true,
                'ò' => true,
                'ó' => true,
                'ô' => true,
                'õ' => true,
                'ö' => true,
                'ø' => true,
                'ō' => true,
                'ŏ' => true,
                'ő' => true,
                'œ' => true,
                'ơ' => true,
                'ǒ' => true,
                'ǫ' => true,
                'ǭ' => true,
                'ǿ' => true,
                'ȍ' => true,
                'ȏ' => true,
                'ȫ' => true,
                'ȭ' => true,
                'ȯ' => true,
                'ȱ' => true,
                'ɔ' => true,
                'ω' => true,
                'о' => true,
                'ө' => true,
                'ṍ' => true,
                'ṏ' => true,
                'ṑ' => true,
                'ṓ' => true,
                'ọ' => true,
                'ỏ' => true,
                'ố' => true,
                'ồ' => true,
                'ổ' => true,
                'ỗ' => true,
                'ộ' => true,
                'ớ' => true,
                'ờ' => true,
                'ở' => true,
                'ỡ' => true,
                'ợ' => true,
            ],

            'u' => [
                'u' => true,
                'y' => true,
                'ù' => true,
                'ú' => true,
                'û' => true,
                'ü' => true,
                'ý' => true,
                'ÿ' => true,
                'ũ' => true,
                'ū' => true,
                'ŭ' => true,
                'ů' => true,
                'ű' => true,
                'ų' => true,
                'ŷ' => true,
                'ư' => true,
                'ǔ' => true,
                'ǖ' => true,
                'ǘ' => true,
                'ǚ' => true,
                'ǜ' => true,
                'ȕ' => true,
                'ȗ' => true,
                'ȳ' => true,
                'ɏ' => true,
                'ʊ' => true,
                'у' => true,
                'ю' => true,
                'ў' => true,
                'ү' => true,
                'ӱ' => true,
                'ӳ' => true,
                'ṳ' => true,
                'ṵ' => true,
                'ṷ' => true,
                'ṹ' => true,
                'ṻ' => true,
                'ụ' => true,
                'ủ' => true,
                'ứ' => true,
                'ừ' => true,
                'ử' => true,
                'ữ' => true,
                'ự' => true,
                'ỳ' => true,
            ],
        ];

        return $list;
    }


    public function is_utf8(string $str) : bool
    {
        // > gzhegow, not sure, but check below works the same
        // return preg_match(
        //     '%(?:'
        //     . '[\xC2-\xDF][\x80-\xBF]'             // > non-overlong 2-byte
        //     . '|\xE0[\xA0-\xBF][\x80-\xBF]'        // > excluding overlongs
        //     . '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}' // > straight 3-byte
        //     . '|\xED[\x80-\x9F][\x80-\xBF]'        // > excluding surrogates
        //     . '|\xF0[\x90-\xBF][\x80-\xBF]{2}'     // > planes 1-3
        //     . '|[\xF1-\xF3][\x80-\xBF]{3}'         // > planes 4-15
        //     . '|\xF4[\x80-\x8F][\x80-\xBF]{2}'     // > plane 16
        //     . ')+%xs',
        //     $str
        // ) === 1;

        return preg_match('//u', $str) === 1;
    }


    /**
     * > возвращает число символов в строке
     *
     * @return int|float
     */
    public function strlen($value, ?string $mb_encoding = null) // : int|NAN
    {
        if ( ! is_string($value) ) {
            return NAN;
        }

        if ( '' === $value ) {
            return 0;
        }

        $len = $this->staticMbstring()
            ? ((null !== $mb_encoding)
                ? mb_strlen($value, $mb_encoding)
                : mb_strlen($value)
            )
            : count(preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY));

        return $len;
    }

    /**
     * > возвращает размер строки в байтах
     *
     * @return int|float
     */
    public function strsize($value) // : int|NAN
    {
        if ( ! is_string($value) ) {
            return NAN;
        }

        if ( '' === $value ) {
            return 0;
        }

        $size = strlen($value);

        return $size;
    }


    /**
     * > заменяет все буквы на малые
     */
    public function lower(string $string, ?string $mb_encoding = null) : string
    {
        if ( $this->staticMbstring() ) {
            $result = (null !== $mb_encoding)
                ? mb_strtolower($string, $mb_encoding)
                : mb_strtolower($string);

        } else {
            if ( $this->is_utf8($string) ) {
                throw new RuntimeException(
                    [
                        ''
                        . 'The `string` contains UTF-8 symbols'
                        . 'but `staticMbstring()` returned that multibyte features is disabled',
                    ]
                );
            }

            $result = strtolower($string);
        }

        return $result;
    }

    /**
     * > заменяет все буквы на большие
     */
    public function upper(string $string, ?string $mb_encoding = null) : string
    {
        if ( $this->staticMbstring() ) {
            $result = (null !== $mb_encoding)
                ? mb_strtoupper($string, $mb_encoding)
                : mb_strtoupper($string);

        } else {
            if ( $this->is_utf8($string) ) {
                throw new RuntimeException(
                    [
                        ''
                        . 'The `string` contains UTF-8 symbols'
                        . 'but `staticMbstring()` returned that multibyte features is disabled',
                    ]
                );
            }

            $result = strtoupper($string);
        }

        return $result;
    }


    /**
     * > пишет слово с малой буквы
     */
    public function lcfirst(string $string, ?string $mb_encoding = null) : string
    {
        $theMb = Lib::mb();

        if ( $this->staticMbstring() ) {
            $result = $theMb->lcfirst($string, $mb_encoding);

        } else {
            if ( $this->is_utf8($string) ) {
                throw new RuntimeException(
                    [
                        ''
                        . 'The `string` contains UTF-8 symbols'
                        . 'but `staticMbstring()` returned that multibyte features is disabled',
                    ]
                );
            }

            $result = lcfirst($string);
        }

        return $result;
    }

    /**
     * > пишет слово с большой буквы
     */
    public function ucfirst(string $string, ?string $mb_encoding = null) : string
    {
        $theMb = Lib::mb();

        if ( $this->staticMbstring() ) {
            $result = $theMb->ucfirst($string, $mb_encoding);

        } else {
            if ( $this->is_utf8($string) ) {
                throw new RuntimeException(
                    [
                        ''
                        . 'The `string` contains UTF-8 symbols'
                        . 'but `staticMbstring()` returned that multibyte features is disabled',
                    ]
                );
            }

            $result = ucfirst($string);
        }

        return $result;
    }


    /**
     * > пишет каждое слово в предложении с малой буквы
     */
    public function lcwords(string $string, ?string $separators = null, ?string $mb_encoding = null) : string
    {
        $separators = $separators ?? " \t\r\n\f\v";

        $thePreg = Lib::preg();

        $regex = $thePreg->preg_quote_ord($separators, $mb_encoding);
        $regex = '/(^|[' . $regex . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[1];
                $last = $this->lcfirst($m[2], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }

    /**
     * > пишет каждое слово в предложении с большой буквы
     */
    public function ucwords(string $string, ?string $separators = null, ?string $mb_encoding = null) : string
    {
        $separators = $separators ?? " \t\r\n\f\v";

        $thePreg = Lib::preg();

        $regex = $thePreg->preg_quote_ord($separators, $mb_encoding);
        $regex = '/(^|[' . $regex . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[1];
                $last = $this->ucfirst($m[2], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }


    public function str_split(string $string, ?int $length = null, ?string $mb_encoding = null) : array
    {
        $length = $length ?? 1;

        $theMb = Lib::mb();
        $theType = Lib::type();

        $lengthInt = $theType->int_positive($length)->orThrow();

        if ( $this->staticMbstring() ) {
            $result = $theMb->str_split($string, $lengthInt, $mb_encoding);

        } else {
            $result = preg_split("/(?<=.{{$length}})/u", $string, -1, PREG_SPLIT_NO_EMPTY);
        }

        return $result;
    }


    public function str_starts(
        string $string, string $needle, ?bool $ignoreCase = null,
        array $refs = []
    ) : bool
    {
        $withSubstr = array_key_exists(0, $refs);
        if ( $withSubstr ) {
            $refSubstr =& $refs[0];
        }
        $refSubstr = null;

        $ignoreCase = $ignoreCase ?? true;

        if ( '' === $string ) return false;
        if ( '' === $needle ) {
            $refSubstr = $string;

            return true;
        }

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

        $pos = $fnStrpos($string, $needle);
        $status = (0 === $pos);

        if ( $status && $withSubstr ) {
            $refSubstr = $fnSubstr($string, $fnStrlen($needle));
        }

        unset($refSubstr);

        return $status;
    }

    public function str_ends(
        string $string, string $needle, ?bool $ignoreCase = null,
        array $refs = []
    ) : bool
    {
        $withSubstr = array_key_exists(0, $refs);
        if ( $withSubstr ) {
            $refSubstr =& $refs[0];
        }
        $refSubstr = null;

        $ignoreCase = $ignoreCase ?? true;

        if ( '' === $string ) return false;
        if ( '' === $needle ) {
            $refSubstr = $string;

            return false;
        }

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrrpos = $ignoreCase
            ? $this->mb_func('strripos')
            : $this->mb_func('strrpos');

        $pos = $fnStrrpos($string, $needle);
        $status = ($pos === $fnStrlen($string) - $fnStrlen($needle));

        if ( $status && $withSubstr ) {
            $refSubstr = $fnSubstr($string, 0, $pos);
        }

        unset($refSubstr);

        return $status;
    }


    /**
     * > обрезает у строки подстроку с начала (ltrim, только для строк а не букв)
     */
    public function lcrop(string $string, string $needle, ?bool $ignoreCase = null, ?int $limit = null) : string
    {
        $limit = $limit ?? -1;
        $ignoreCase = $ignoreCase ?? true;

        if ( '' === $string ) return $string;
        if ( '' === $needle ) return $string;
        if ( 0 === $limit ) return $string;

        if ( $limit < -1 ) {
            throw new LogicException(
                'The `limit` should be GTE -1',
                $limit
            );
        }

        $result = $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

        $pos = $fnStrpos($result, $needle);

        while ( $pos === 0 ) {
            if ( 0 === $limit-- ) {
                break;
            }

            $result = $fnSubstr($result,
                $fnStrlen($needle)
            );

            $pos = $fnStrpos($result, $needle);
        }

        return $result;
    }

    /**
     * > обрезает у строки подстроку с конца (rtrim, только для строк а не букв)
     */
    public function rcrop(string $string, string $needle, ?bool $ignoreCase = null, ?int $limit = null) : string
    {
        $limit = $limit ?? -1;
        $ignoreCase = $ignoreCase ?? true;

        if ( '' === $string ) return $string;
        if ( '' === $needle ) return $string;
        if ( 0 === $limit ) return $string;

        if ( $limit < -1 ) {
            throw new LogicException(
                'The `limit` should be GTE -1',
                $limit
            );
        }

        $result = $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrrpos = $ignoreCase
            ? $this->mb_func('strripos')
            : $this->mb_func('strrpos');

        $pos = $fnStrrpos($result, $needle);

        while ( $pos === ($fnStrlen($result) - $fnStrlen($needle)) ) {
            if ( 0 === $limit-- ) {
                break;
            }

            $result = $fnSubstr($result, 0, $pos);

            $pos = $fnStrrpos($result, $needle);
        }

        return $result;
    }

    /**
     * > обрезает у строки подстроки с обеих сторон (trim, только для строк а не букв)
     */
    public function crop(string $string, $crops, ?bool $ignoreCase = null, $limits = null) : string
    {
        $thePhp = Lib::php();

        $cropsList = $thePhp->to_list($crops);
        $limitsList = $thePhp->to_list($limits ?? [ -1 ]);

        if ( [] === $cropsList ) {
            return $string;
        }

        if ( [] === $limitsList ) {
            throw new LogicException(
                'The `limits` should be array of integers or be null',
                $limits
            );
        }

        $needleLcrop = array_shift($cropsList);
        $needleRcrop = ([] !== $cropsList)
            ? array_shift($cropsList)
            : $needleLcrop;

        $limitLcrop = array_shift($limitsList);
        $limitRcrop = ([] !== $limitsList)
            ? array_shift($limitsList)
            : $limitLcrop;

        $result = $string;
        $result = $this->lcrop($result, $needleLcrop, $ignoreCase, $limitLcrop);
        $result = $this->rcrop($result, $needleRcrop, $ignoreCase, $limitRcrop);

        return $result;
    }


    /**
     * > добавляет подстроку в начало строки, если её уже там нет
     */
    public function unlcrop(string $string, string $needle, ?int $times = null, ?bool $isIgnoreCase = null) : string
    {
        $times = $times ?? 1;
        $isIgnoreCase = $isIgnoreCase ?? true;

        if ( '' === $needle ) return $string;
        if ( 0 === $times ) return $string;

        if ( $times < 1 ) {
            throw new LogicException(
                'The `times` should be GTE 1',
                $times
            );
        }

        $result = $string;
        $result = $this->lcrop($result, $needle, $isIgnoreCase, -1);
        $result = str_repeat($needle, $times) . $result;

        return $result;
    }

    /**
     * > добавляет подстроку в конец строки, если её уже там нет
     */
    public function unrcrop(string $string, string $needle, ?int $times = null, ?bool $isIgnoreCase = null) : string
    {
        $times = $times ?? 1;
        $isIgnoreCase = $isIgnoreCase ?? true;

        if ( '' === $needle ) return $string;
        if ( 0 === $times ) return $string;

        if ( $times < 1 ) {
            throw new LogicException(
                'The `times` should be GTE 1',
                $times
            );
        }

        $result = $string;
        $result = $this->rcrop($result, $needle, $isIgnoreCase, -1);
        $result = $result . str_repeat($needle, $times);

        return $result;
    }

    /**
     * > оборачивает строку в подстроки, если их уже там нет
     *
     * @param string|string[] $crops
     * @param int|int[]       $times
     */
    public function uncrop(string $string, $crops, $times = null, ?bool $ignoreCase = null) : string
    {
        $thePhp = Lib::php();

        $cropsList = $thePhp->to_list($crops);
        $timesList = $thePhp->to_list($times ?? [ 1 ]);

        if ( [] === $cropsList ) {
            return $string;
        }

        if ( [] === $timesList ) {
            throw new LogicException(
                'The `times` should be array of integers or be null',
                $times
            );
        }

        $needleLcrop = array_shift($cropsList);
        $needleRcrop = ([] !== $cropsList)
            ? array_shift($cropsList)
            : $needleLcrop;

        $timesLcrop = array_shift($timesList);
        $timesRcrop = ([] !== $timesList)
            ? array_shift($timesList)
            : $timesLcrop;

        $result = $string;
        $result = $this->unlcrop($result, $needleLcrop, $timesLcrop, $ignoreCase);
        $result = $this->unrcrop($result, $needleRcrop, $timesRcrop, $ignoreCase);

        return $result;
    }


    /**
     * > str_replace с поддержкой limit замен
     *
     * @param string|string[] $search
     * @param string|string[] $replace
     * @param string|string[] $subject
     *
     * @return string|string[]
     */
    public function str_replace_limit(
        $search, $replace, $subject,
        ?int $limit = null,
        ?int &$refCount = null
    )
    {
        $thePhp = Lib::php();

        $searchList = $thePhp->to_list($search);
        $replaceList = $thePhp->to_list($replace);
        $subjectList = $thePhp->to_list($subject);

        if ( [] === $searchList ) {
            return $subject;
        }
        if ( [] === $replaceList ) {
            return $subject;
        }
        if ( [] === $subjectList ) {
            return [];
        }

        $_regexes = [];
        foreach ( $searchList as $i => $s ) {
            $regex = preg_quote($s, '/');
            $regex = '/' . $regex . '/u';

            $_regexes[$i] = $regex;
        }

        $result = preg_replace($_regexes, $replace, $subject, $limit, $refCount);

        return $result;
    }

    /**
     * > str_ireplace с поддержкой limit замен
     *
     * @param string|string[] $search
     * @param string|string[] $replace
     * @param string|string[] $subject
     *
     * @return string|string[]
     */
    public function str_ireplace_limit(
        $search, $replace, $subject,
        ?int $limit = null,
        ?int &$refCount = null
    )
    {
        $thePhp = Lib::php();

        $searchList = $thePhp->to_list($search);
        $replaceList = $thePhp->to_list($replace);
        $subjectList = $thePhp->to_list($subject);

        if ( [] === $searchList ) {
            return $subject;
        }
        if ( [] === $replaceList ) {
            return $subject;
        }
        if ( [] === $subjectList ) {
            return [];
        }

        $_regexes = [];
        foreach ( $searchList as $i => $s ) {
            $regex = preg_quote($s, '/');
            $regex = '/' . $regex . '/iu';

            $_regexes[$i] = $regex;
        }

        $result = preg_replace($_regexes, $replace, $subject, $limit, $refCount);

        return $result;
    }


    /**
     * @param string|string[] $lines
     */
    public function str_match(
        string $pattern, $lines,
        ?string $wildcardLetterSequence = null,
        ?string $wildcardSeparator = null,
        ?string $wildcardLetterSingle = null
    ) : array
    {
        if ( '' === $pattern ) {
            return [];
        }

        $thePhp = Lib::php();

        $linesList = $thePhp->to_list($lines);

        if ( [] === $linesList ) {
            return [];
        }

        $regex = $this->str_match_regex(
            $pattern,
            $wildcardLetterSequence,
            $wildcardSeparator,
            $wildcardLetterSingle
        );

        $regex = "/^{$regex}$/u";

        $result = [];

        foreach ( $linesList as $line ) {
            if ( preg_match($regex, $line) ) {
                $result[] = $line;
            }
        }

        return $result;
    }

    /**
     * @param string|string[] $lines
     */
    public function str_match_starts(
        string $pattern, $lines,
        ?string $wildcardLetterSequence = null,
        ?string $wildcardSeparator = null,
        ?string $wildcardLetterSingle = null
    ) : array
    {
        if ( '' === $pattern ) {
            return [];
        }

        $thePhp = Lib::php();

        $linesList = $thePhp->to_list($lines);

        if ( [] === $linesList ) {
            return [];
        }

        $regex = $this->str_match_regex(
            $pattern,
            $wildcardLetterSequence,
            $wildcardSeparator,
            $wildcardLetterSingle
        );

        $regex = "/^{$regex}/u";

        $result = [];

        foreach ( $linesList as $line ) {
            if ( preg_match($regex, $line) ) {
                $result[] = $line;
            }
        }

        return $result;
    }

    /**
     * @param string|string[] $lines
     */
    public function str_match_ends(
        string $pattern, $lines,
        ?string $wildcardLetterSequence = null,
        ?string $wildcardSeparator = null,
        ?string $wildcardLetterSingle = null
    ) : array
    {
        if ( '' === $pattern ) {
            return [];
        }

        $thePhp = Lib::php();

        $linesList = $thePhp->to_list($lines);

        if ( [] === $linesList ) {
            return [];
        }

        $regex = $this->str_match_regex(
            $pattern,
            $wildcardLetterSequence,
            $wildcardSeparator,
            $wildcardLetterSingle
        );

        $regex = "/{$regex}$/u";

        $result = [];

        foreach ( $linesList as $line ) {
            if ( preg_match($regex, $line) ) {
                $result[] = $line;
            }
        }

        return $result;
    }

    /**
     * @param string|string[] $lines
     */
    public function str_match_contains(
        string $pattern, $lines,
        ?string $wildcardLetterSequence = null,
        ?string $wildcardSeparator = null,
        ?string $wildcardLetterSingle = null
    ) : array
    {
        if ( '' === $pattern ) {
            return [];
        }

        $thePhp = Lib::php();

        $linesList = $thePhp->to_list($lines);

        if ( [] === $linesList ) {
            return [];
        }

        $regex = $this->str_match_regex(
            $pattern,
            $wildcardLetterSequence,
            $wildcardSeparator,
            $wildcardLetterSingle
        );

        $regex = "/{$regex}/u";

        $result = [];

        foreach ( $linesList as $line ) {
            if ( preg_match($regex, $line) ) {
                $result[] = $line;
            }
        }

        return $result;
    }

    protected function str_match_regex(
        string $pattern,
        ?string $wildcardSequenceSymbol = null,
        ?string $wildcardSeparatorSymbol = null,
        ?string $wildcardSingleSymbol = null
    ) : string
    {
        if ( '' === $pattern ) {
            return '';
        }

        $thePreg = Lib::preg();

        $hasWildcardSeparatorSymbol = (null !== $wildcardSeparatorSymbol);
        $hasWildcardSequenceSymbol = (null !== $wildcardSequenceSymbol);
        $hasWildcardSingleSymbol = (null !== $wildcardSingleSymbol);

        $testUnique = [];
        if ( $hasWildcardSeparatorSymbol ) {
            $wildcardSeparatorSymbolString = $this->type_char($wildcardSeparatorSymbol)->orThrow();

            $testUnique[] = $wildcardSeparatorSymbolString;
        }
        if ( $hasWildcardSequenceSymbol ) {
            $wildcardSequenceSymbolString = $this->type_char($wildcardSequenceSymbol)->orThrow();

            $testUnique[] = $wildcardSequenceSymbolString;
        }
        if ( $hasWildcardSingleSymbol ) {
            $wildcardSingleSymbolString = $this->type_char($wildcardSingleSymbol)->orThrow();

            $testUnique[] = $wildcardSingleSymbolString;
        }

        if ( count(array_unique($testUnique)) !== count($testUnique) ) {
            throw new LogicException(
                [
                    'The wildcards should be different letters or nulls',
                    $wildcardSeparatorSymbol,
                    $wildcardSequenceSymbol,
                    $wildcardSingleSymbol,
                ]
            );
        }

        $_pattern = $pattern;

        $notASymbolRegex = '';

        if ( $hasWildcardSeparatorSymbol ) {
            $wildcardSeparatorRegex = $thePreg->preg_quote_ord($wildcardSeparatorSymbolString);

            $notASymbolRegex .= $wildcardSeparatorRegex;
        }
        if ( $hasWildcardSequenceSymbol ) {
            $wildcardLetterSequenceRegex = $thePreg->preg_quote_ord($wildcardSequenceSymbolString);

            $notASymbolRegex .= $wildcardLetterSequenceRegex;
        }
        if ( $hasWildcardSingleSymbol ) {
            $wildcardLetterSingleRegex = $thePreg->preg_quote_ord($wildcardSingleSymbolString);

            $notASymbolRegex .= $wildcardLetterSingleRegex;
        }

        if ( '' === $notASymbolRegex ) {
            $anySymbolRegex = '.';

        } else {
            $anySymbolRegex = '[^' . $notASymbolRegex . ']';
        }

        $replacements = [];

        if ( $hasWildcardSeparatorSymbol ) {
            $replacement = '{{ 1 }}';
            $replacements[preg_quote($replacement, '/')] = $wildcardSeparatorRegex;

            $_pattern = str_replace(
                $wildcardSeparatorSymbolString,
                $replacement,
                $_pattern
            );
        }
        if ( $hasWildcardSequenceSymbol ) {
            $replacement = '{{ 2 }}';
            $replacements[preg_quote($replacement, '/')] = $anySymbolRegex . '+';

            $_pattern = str_replace(
                $wildcardSequenceSymbolString,
                $replacement,
                $_pattern
            );
        }
        if ( $hasWildcardSingleSymbol ) {
            $replacement = '{{ 3 }}';
            $replacements[preg_quote($replacement, '/')] = $anySymbolRegex;

            $_pattern = str_replace(
                $wildcardSingleSymbolString,
                $replacement,
                $_pattern
            );
        }

        $_pattern = preg_quote($_pattern, '/');

        if ( [] !== $replacements ) {
            $_pattern = strtr($_pattern, $replacements);
        }

        $patternRegex = "/{$_pattern}/";

        if ( false === preg_match($patternRegex, '') ) {
            throw new RuntimeException(
                'Invalid regex for `str_match`: ' . $patternRegex
            );
        }

        return $_pattern;
    }


    /**
     * > 'theCamelCase'
     */
    public function camel(string $string) : string
    {
        if ( '' === $string ) return '';

        $result = $string;

        $regex = '/[^\p{L}\d]+([\p{L}\d])/iu';

        $result = preg_replace_callback($regex, function ($m) {
            return $this->mb_func('strtoupper')($m[1]);
        }, $result);

        $result = $this->lcfirst($result);

        return $result;
    }

    /**
     * > 'ThePascalCase'
     */
    public function pascal(string $string) : string
    {
        if ( '' === $string ) return '';

        $result = $string;

        $regex = '/[^\p{L}\d]+([\p{L}\d])/iu';

        $result = preg_replace_callback($regex, function ($m) {
            return $this->mb_func('strtoupper')($m[1]);
        }, $result);

        $result = $this->ucfirst($result);

        return $result;
    }


    /**
     * > 'the Space case'
     */
    public function space(string $string) : string
    {
        if ( '' === $string ) return '';

        $result = $string;

        $regex = '/[^\p{L}\d ]+/iu';

        $result = preg_replace($regex, ' ', $result);

        $regex = '/(?<=[^\p{Lu} ])(?=\p{Lu})/u';

        $result = preg_replace($regex, ' $2', $result);

        return $result;
    }

    /**
     * > 'the_Snake_case'
     */
    public function snake(string $string) : string
    {
        if ( '' === $string ) return '';

        $result = $string;

        $regex = '/[^\p{L}\d_]+/iu';

        $result = preg_replace($regex, '_', $result);

        $regex = '/(?<=[^\p{Lu}_])(?=\p{Lu})/u';

        $result = preg_replace($regex, '_$2', $result);

        return $result;
    }

    /**
     * > 'the-Kebab-case'
     */
    public function kebab(string $string) : string
    {
        if ( '' === $string ) return '';

        $result = $string;

        $regex = '/[^\p{L}\d-]+/iu';

        $result = preg_replace($regex, '-', $result);

        $regex = '/(?<=[^\p{Lu}-])(?=\p{Lu})/u';

        $result = preg_replace($regex, '-', $result);

        return $result;
    }


    /**
     * > 'the space case'
     */
    public function space_lower(string $string) : string
    {
        $result = $string;
        $result = $this->space($result);
        $result = $this->lower($result);

        return $result;
    }

    /**
     * > 'the_snake_case'
     */
    public function snake_lower(string $string) : string
    {
        $result = $string;
        $result = $this->snake($result);
        $result = $this->lower($result);

        return $result;
    }

    /**
     * > 'the-kebab-case'
     */
    public function kebab_lower(string $string) : string
    {
        $result = $string;
        $result = $this->kebab($result);
        $result = $this->lower($result);

        return $result;
    }


    /**
     * > 'THE SPACE CASE'
     */
    public function space_upper(string $string) : string
    {
        $result = $string;
        $result = $this->space($result);
        $result = $this->upper($result);

        return $result;
    }

    /**
     * > 'THE_SNAKE_CASE'
     */
    public function snake_upper(string $string) : string
    {
        $result = $string;
        $result = $this->snake($result);
        $result = $this->upper($result);

        return $result;
    }

    /**
     * > 'THE-KEBAB-CASE'
     */
    public function kebab_upper(string $string) : string
    {
        $result = $string;
        $result = $this->kebab($result);
        $result = $this->upper($result);

        return $result;
    }


    /**
     * > 'привет мир' -> 'nPuBeT Mup'
     * > '+привет +мир +100 abc' -> '+nPuBeT +Mup +100 ???'
     *
     * @param array|string $ignoreSymbols
     */
    public function translit_ru2ascii(string $string, ?string $delimiter = null, $ignoreSymbols = null) : string
    {
        if ( '' === $string ) {
            return '';
        }

        $theMb = Lib::mb();
        $thePhp = Lib::php();
        $thePreg = Lib::preg();
        $theType = Lib::type();

        $dictionary = [
            'а' => 'a',
            'б' => '6',
            'в' => 'B',
            'г' => 'r',
            'д' => 'g',
            'е' => 'e',
            'ж' => ']![',
            'з' => '3',
            'и' => 'u',
            'й' => 'u',
            'к' => 'K',
            'л' => '/l',
            'м' => 'M',
            'н' => 'H',
            'о' => 'o',
            'п' => 'n',
            'р' => 'p',
            'с' => 'c',
            'т' => 'T',
            'у' => 'y',
            'ф' => 'qp',
            'х' => 'x',
            'ц' => 'll,',
            'ч' => '4',
            'ш' => 'lll',
            'щ' => 'lll,',
            'ъ' => "`b",
            'ы' => 'bl',
            'ь' => 'b',
            'э' => '3}',
            'ю' => '!0',
            'я' => '9l',
            'ё' => 'e',
            //
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
        ];

        if ( null !== $delimiter ) {
            $delimiterChar = $theType->char($delimiter)->orThrow();

            $theType->key_not_exists($delimiterChar, $dictionary)->orThrow();
        }

        $gen = $thePhp->to_list_it($ignoreSymbols);

        $ignoreSymbolsIndex = [];
        foreach ( $gen as $str ) {
            if ( is_array($str) ) {
                continue;

            } elseif ( $theType->letter($str)->isOk([ &$letter ]) ) {
                $letterLower = mb_strtolower($letter);

                $ignoreSymbolsIndex[$letterLower] = true;

            } else {
                throw new LogicException(
                    [ 'Each of `ignoreSymbols` should be a letter', $str ]
                );
            }
        }

        $ignoreSymbolsRegex = array_keys($ignoreSymbolsIndex);
        $ignoreSymbolsRegex = implode('', $ignoreSymbolsRegex);
        $ignoreSymbolsRegex = $thePreg->preg_quote_ord($ignoreSymbolsRegex);

        $stringLower = mb_strtolower($string);

        $stringLower = preg_replace('/\s/u', ' ', $stringLower);

        $result = preg_replace_callback(
            "/[^а-яё0-9{$ignoreSymbolsRegex} ]/u",
            static function ($m) use ($delimiter) {
                return $delimiter
                    ?? ('{' . $m[0] . '}');
            },
            $stringLower
        );

        $result = str_replace(
            array_keys($dictionary),
            array_values($dictionary),
            $result
        );

        $result = trim($result, ' ');

        return $result;
    }


    /**
     * > обычный трим завернутый в генератор
     *
     * @return \Generator<string>
     */
    public function trim_it($strings, ?string $characters = null) : \Generator
    {
        $characters = $characters ?? " \n\r\t\v\0";

        $thePhp = Lib::php();

        foreach ( $thePhp->to_iterable($strings) as $string ) {
            if ( ! is_string($string) ) {
                throw new LogicException(
                    [ 'Each of `strings` should be a string', $string ]
                );
            }

            yield trim($string, $characters);
        }
    }

    /**
     * > обычный трим завернутый в генератор
     *
     * @return \Generator<string>
     */
    public function ltrim_it($strings, ?string $characters = null) : \Generator
    {
        $characters = $characters ?? " \n\r\t\v\0";

        $thePhp = Lib::php();

        foreach ( $thePhp->to_iterable($strings) as $string ) {
            if ( ! is_string($string) ) {
                throw new LogicException(
                    [ 'Each of `strings` should be a string', $string ]
                );
            }

            yield ltrim($string, $characters);
        }
    }

    /**
     * > обычный трим завернутый в генератор
     *
     * @return \Generator<string>
     */
    public function rtrim_it($strings, ?string $characters = null) : \Generator
    {
        $characters = $characters ?? " \n\r\t\v\0";

        $thePhp = Lib::php();

        foreach ( $thePhp->to_iterable($strings) as $string ) {
            if ( ! is_string($string) ) {
                throw new LogicException(
                    [ 'Each of `strings` should be a string', $string ]
                );
            }

            yield rtrim($string, $characters);
        }
    }


    /**
     * > урезает английское слово до префикса из нескольких букв - когда имя индекса в бд слишком длинное
     * > оставляет одну гласную
     *
     * > hello/3 -> hel
     * > bsod/3 -> bso
     * > manufacturer/6 -> manfct
     */
    public function prefix(string $string, ?int $length = null) : string
    {
        if ( '' === $string ) {
            return '';
        }

        $length = $length ?? 3;

        if ( $length < 1 ) {
            throw new LogicException(
                [ 'The `length` should be GT 0', $length ]
            );
        }

        $theStr = Lib::str();

        $isUnicodeAllowed = $theStr->staticMbstring();

        $_string = $isUnicodeAllowed
            ? preg_replace('/(?:[^\w]|[_])+/u', '', $string)
            : preg_replace('/(?:[^\w]|[_])+/', '', $string);

        if ( '' === $_string ) {
            throw new LogicException(
                [ 'The `string` should contain at least one letter', $string ]
            );
        }

        $fnStrlen = $theStr->mb_func('strlen');
        $fnSubstr = $theStr->mb_func('substr');

        $source = $_string;
        $sourceLen = $fnStrlen($source);

        $_length = min($length, $sourceLen);

        if ( 0 === $_length ) {
            return '';
        }

        $vowels = '';
        $vowelsArray = $this->loadVowels();
        foreach ( $vowelsArray as $vowelIndex ) {
            $vowels .= implode('', array_keys($vowelIndex));
        }

        $sourceConsonants = [];
        $sourceVowels = [];
        for ( $i = 0; $i < $sourceLen; $i++ ) {
            $letter = $fnSubstr($source, $i, 1);

            ('' === trim($letter, $vowels))
                ? ($sourceVowels[$i] = $letter)
                : ($sourceConsonants[] = $letter);
        }

        $letters = [];

        $hasVowel = false;
        $left = $_length;
        for ( $i = 0; $i < $_length; $i++ ) {
            $letter = null;
            if ( isset($sourceVowels[$i]) ) {
                if ( ! $hasVowel ) {
                    $letter = $sourceVowels[$i];
                    $hasVowel = true;

                } elseif ( $left > count($sourceConsonants) ) {
                    $letter = $sourceVowels[$i];
                }
            }

            $letter = $letter ?? array_shift($sourceConsonants);
            $left--;

            $letters[] = $letter;
        }

        $result = implode('', $letters);

        return $result;
    }


    public function lines(string $text) : array
    {
        $lines = explode("\n", $text);

        foreach ( $lines as $i => $line ) {
            $line = rtrim($line);

            $lines[$i] = $line;
        }

        return $lines;
    }

    public function eol(string $text, $eol = null, array $refs = []) : string
    {
        $withLines = array_key_exists(0, $refs);
        if ( $withLines ) {
            $refLines =& $refs[0];
        }
        $refLines = null;

        $_eol = $eol ?? "\n";
        $_eol = (string) $_eol;

        $refLines = $this->lines($text);

        $output = implode($_eol, $refLines);

        unset($refLines);

        return $output;
    }


    /**
     * > конвертирует непечатаемые символы терминала Windows (`кракозябры`) в последовательности, которые можно прочесть визуально
     */
    public function utf8_encode(string $string, ?string $encoding = null) : ?string
    {
        if ( ! \function_exists('iconv') ) {
            throw new ExtensionException(
                [ 'Missing PHP extension: iconv' ]
            );
        }

        $encodingString = null
            ?? $encoding
            ?? (\ini_get('php.output_encoding') ?: null)
            ?? (\ini_get('default_charset') ?: null)
            ?? 'UTF-8';

        $stringConverted = @iconv($encodingString, 'UTF-8', $string);
        if ( false !== $stringConverted ) {
            return $stringConverted;
        }

        if ( 'CP1252' !== $encodingString ) {
            $stringConverted = @iconv('CP1252', 'UTF-8', $string);
            if ( false !== $stringConverted ) {
                return $stringConverted;
            }
        }

        if ( 'CP850' !== $encodingString ) {
            $stringConverted = @iconv('CP850', 'UTF-8', $string);
            if ( false !== $stringConverted ) {
                return $stringConverted;
            }
        }

        return null;
    }

    /**
     * > конвертирует непечатаемые символы строки в байтовые и hex последовательности, которые можно прочесть
     */
    public function dump_encode(string $string, ?string $encoding = null) : ?string
    {
        $result = $string;

        $asciiControlsNoTrims = $this->loadAsciiControlsNoTrims(true);
        $asciiControlsOnlyTrims = $this->loadAsciiControlsOnlyTrims(false);

        $foundBinary = false;
        $count = 0;
        $result = str_replace(
            array_keys($asciiControlsNoTrims),
            array_values($asciiControlsNoTrims),
            $result,
            $count
        );
        if ( $count ) {
            $foundBinary = true;
        }

        $isUtf8 = $this->is_utf8($result);
        if ( $isUtf8 ) {
            $invisibles = $this->loadInvisibles();

            $count = 0;

            $result = str_replace(
                array_keys($invisibles),
                array_values($invisibles),
                $result,
                $count
            );

            if ( $count ) {
                $foundBinary = true;
            }

        } else {
            $_varUtf8 = $this->utf8_encode($result, $encoding);

            if ( $_varUtf8 !== $result ) {
                $result = $_varUtf8;

                $foundBinary = true;
            }
        }

        if ( $foundBinary ) {
            $result = "b`{$result}`";
        }

        foreach ( $asciiControlsOnlyTrims as $i => $v ) {
            if ( $i === "\n" ) {
                $asciiControlsOnlyTrims[$i] .= $i;
            }
        }
        $result = str_replace(
            array_keys($asciiControlsOnlyTrims),
            array_values($asciiControlsOnlyTrims),
            $result
        );

        return $result;
    }
}
