<?php
/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Str\Slugger\Slugger;
use Gzhegow\Lib\Modules\Str\Inflector\Inflector;
use Gzhegow\Lib\Modules\Str\Slugger\SluggerInterface;
use Gzhegow\Lib\Modules\Str\Interpolator\Interpolator;
use Gzhegow\Lib\Modules\Str\Inflector\InflectorInterface;
use Gzhegow\Lib\Modules\Str\Interpolator\InterpolatorInterface;


class StrModule
{
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
     * @var bool
     */
    protected $mbstring = false;

    /**
     * @var array<string, callable-string>
     */
    protected $mbstringFuncMap = [];


    public function __construct()
    {
        $mbstring = extension_loaded('mbstring');

        $this->mbstring = $mbstring;

        $this->mbstringFuncMap[ 'lcfirst' ] = [ Lib::mb(), 'lcfirst' ];
        $this->mbstringFuncMap[ 'ucfirst' ] = [ Lib::mb(), 'ucfirst' ];
        $this->mbstringFuncMap[ 'lcwords' ] = [ Lib::mb(), 'lcwords' ];
        $this->mbstringFuncMap[ 'ucwords' ] = [ Lib::mb(), 'ucwords' ];

        if (PHP_VERSION_ID < 70400) {
            $this->mbstringFuncMap[ 'str_split' ] = [ Lib::mb(), 'str_split' ];
        }
    }


    public function inflector(InflectorInterface $inflector = null) : InflectorInterface
    {
        return $this->inflector = null
            ?? $inflector
            ?? $this->inflector
            ?? new Inflector();
    }

    public function interpolator(InterpolatorInterface $interpolator = null) : InterpolatorInterface
    {
        return $this->interpolator = null
            ?? $interpolator
            ?? $this->interpolator
            ?? new Interpolator();
    }

    public function slugger(SluggerInterface $slugger = null) : SluggerInterface
    {
        return $this->slugger = null
            ?? $slugger
            ?? $this->slugger
            ?? new Slugger();
    }


    public function static_mbstring(bool $mbstring = null) : bool
    {
        if (null !== $mbstring) {
            if ($mbstring) {
                if (! extension_loaded('mbstring')) {
                    throw new RuntimeException(
                        'Missing PHP extension: mbstring'
                    );
                }
            }

            $last = $this->mbstring;

            $current = $mbstring;

            $this->mbstring = $current;

            $result = $last;
        }

        $result = $result ?? $this->mbstring;

        return $result;
    }


    /**
     * @param string   $fnName
     * @param callable $fn
     *
     * @return static
     */
    public function mb_func_register(string $fnName, $fn)
    {
        if (isset($this->mbstringFuncMap[ $fnName ])) {
            throw new LogicException(
                [ 'The `fnName` is already registered', $fnName ]
            );
        }

        $this->mbstringFuncMap[ $fnName ] = $fn;

        return $this;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable
     */
    public function mb_func(string $fn)
    {
        if (! $this->static_mbstring()) {
            return $fn;
        }

        $result = null
            ?? $this->mbstringFuncMap[ $fn ]
            ?? 'mb_' . $fn;

        return $result;
    }


    /**
     * @param string|null $result
     */
    public function type_string(&$result, $value) : bool
    {
        $result = null;

        $isString = is_string($value);

        if (! $isString) {
            if (
                (null === $value)
                || (is_bool($value))
                || (is_float($value) && (! is_finite($value)))
                || (is_array($value))
                || (is_resource($value))
                || ('resource (closed)' === gettype($value))
                || (Lib::type()->is_nil($value))
            ) {
                // NULL is equal EMPTY STRING but cannot be casted to
                // BOOLEAN is not string
                // NAN, INF, -INF is not string
                // ARRAY is not string
                // RESOURCE is not string
                // CLOSED RESOURCE is not string
                // NIL is not string

                return false;
            }
        }

        $_value = null;

        if ($isString) {
            $_value = $value;

        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $_value = (string) $value;
            }

        } else {
            $settype = $value;
            $status = settype($settype, 'string');
            if ($status) {
                $_value = $settype;
            }
        }

        if (null === $_value) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_string_not_empty(&$result, $value) : bool
    {
        $result = null;

        if (! $this->type_string($_value, $value)) {
            return false;
        }

        if ('' === $_value) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_trim(&$result, $value, string $characters = null) : bool
    {
        $result = null;

        $characters = $characters ?? " \n\r\t\v\0";

        if (! $this->type_string($_value, $value)) {
            return false;
        }

        $_value = trim($_value, $characters);

        if ('' !== $_value) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $result
     */
    public function type_letter(&$result, $value) : bool
    {
        $result = null;

        if (! $this->type_string_not_empty($_value, $value)) {
            return false;
        }

        if (1 === $this->strlen($_value)) {
            $result = $_value;

            return $_value;
        }

        return false;
    }

    /**
     * @param Alphabet|null $result
     */
    public function type_alphabet(&$result, $value) : bool
    {
        $result = null;

        if (! $this->type_string_not_empty($_value, $value)) {
            return false;
        }

        preg_replace('/\s+/', '', $_value, 1, $count);
        if ($count > 0) {
            return false;
        }

        $fnOrd = $this->mb_func('ord');
        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');

        $len = $fnStrlen($_value);
        if ($len <= 1) {
            return false;
        }

        $seen = [];
        $regex = '/[';
        $regexNot = '/[^';
        for ( $i = 0; $i < $len; $i++ ) {
            $letter = $fnSubstr($_value, $i, 1);

            if (isset($seen[ $letter ])) {
                return false;
            }
            $seen[ $letter ] = true;

            $letterRegex = sprintf('\x{%X}', $fnOrd($letter));

            $regex .= $letterRegex;
            $regexNot .= $letterRegex;
        }
        $regex .= ']+/';
        $regexNot .= ']/';

        $alphabet = new Alphabet(
            $_value,
            $len,
            $regex,
            $regexNot
        );

        $result = $alphabet;

        return true;
    }


    public function loadAsciiControls(bool $hex = null) : array
    {
        $hex = $hex ?? false;

        if ($hex) {
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

    public function loadAsciiControlsNoTrims(bool $hex = null) : array
    {
        $list = $this->loadAsciiControls($hex);

        unset($list[ chr(9) ]);
        unset($list[ chr(10) ]);
        unset($list[ chr(11) ]);
        unset($list[ chr(13) ]);

        return $list;
    }

    public function loadAsciiControlsOnlyTrims(bool $hex = null) : array
    {
        $hex = $hex ?? false;

        if ($hex) {
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
    public function strlen($value, string $mb_encoding = null) // : int|NAN
    {
        if (! is_string($value)) {
            return NAN;
        }

        if ('' === $value) {
            return 0;
        }

        $len = $this->static_mbstring()
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
        if (! is_string($value)) {
            return NAN;
        }

        if ('' === $value) {
            return 0;
        }

        $size = strlen($value);

        return $size;
    }


    /**
     * > заменяет все буквы на малые
     */
    public function lower(string $string, string $mb_encoding = null) : string
    {
        if ($this->static_mbstring()) {
            $result = (null !== $mb_encoding)
                ? mb_strtolower($string, $mb_encoding)
                : mb_strtolower($string);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = strtolower($string);
        }

        return $result;
    }

    /**
     * > заменяет все буквы на большие
     */
    public function upper(string $string, string $mb_encoding = null) : string
    {
        if ($this->static_mbstring()) {
            $result = (null !== $mb_encoding)
                ? mb_strtoupper($string, $mb_encoding)
                : mb_strtoupper($string);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = strtoupper($string);
        }

        return $result;
    }


    /**
     * > пишет слово с малой буквы
     */
    public function lcfirst(string $string, string $mb_encoding = null) : string
    {
        if ($this->static_mbstring()) {
            $result = Lib::mb()->lcfirst($string, $mb_encoding);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = lcfirst($string);
        }

        return $result;
    }

    /**
     * > пишет слово с большой буквы
     */
    public function ucfirst(string $string, string $mb_encoding = null) : string
    {
        if ($this->static_mbstring()) {
            $result = Lib::mb()->ucfirst($string, $mb_encoding);

        } else {
            if ($this->is_utf8($string)) {
                throw new RuntimeException(
                    'The `string` contains UTF-8 symbols, but `mb_mode_static()` returns that multibyte features is disabled'
                );
            }

            $result = ucfirst($string);
        }

        return $result;
    }


    /**
     * > пишет каждое слово в предложении с малой буквы
     */
    public function lcwords(string $string, string $separators = " \t\r\n\f\v", string $mb_encoding = null) : string
    {
        $thePreg = Lib::preg();

        $regex = $thePreg->preg_quote_ord($separators, $mb_encoding);
        $regex = '/(^|[' . $regex . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = $this->lcfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }

    /**
     * > пишет каждое слово в предложении с большой буквы
     */
    public function ucwords(string $string, string $separators = " \t\r\n\f\v", string $mb_encoding = null) : string
    {
        $thePreg = Lib::preg();

        $regex = $thePreg->preg_quote_ord($separators, $mb_encoding);
        $regex = '/(^|[' . $regex . '])(\w)/u';

        $result = preg_replace_callback(
            $regex,
            function ($m) use ($mb_encoding) {
                $first = $m[ 1 ];
                $last = $this->ucfirst($m[ 2 ], $mb_encoding);

                return "{$first}{$last}";
            },
            $string
        );

        return $result;
    }


    public function str_split(string $string, int $length = null, string $mb_encoding = null) : array
    {
        $length = $length ?? 1;

        if ($length < 1) {
            throw new LogicException(
                [ 'The `length` must be greater than 0', $length ]
            );
        }

        if ($this->static_mbstring()) {
            $result = Lib::mb()->str_split($string, $length, $mb_encoding);

        } else {
            $result = preg_split("/(?<=.{{$length}})/u", $string, -1, PREG_SPLIT_NO_EMPTY);;
        }

        return $result;
    }


    public function str_starts(
        string $string, string $needle, bool $ignoreCase = null,
        array $refs = []
    ) : bool
    {
        $withSubstr = array_key_exists(0, $refs);

        $refSubstr = null;

        if ($withSubstr) {
            $refSubstr =& $refs[ 0 ];
            $refSubstr = null;
        }

        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return false;
        if ('' === $needle) {
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

        if ($status && $withSubstr) {
            $refSubstr = $fnSubstr($string, $fnStrlen($needle));
        }

        unset($refSubstr);

        return $status;
    }

    public function str_ends(
        string $string, string $needle, bool $ignoreCase = null,
        array $refs = []
    ) : bool
    {
        $withSubstr = array_key_exists(0, $refs);

        $refSubstr = null;

        if ($withSubstr) {
            $refSubstr =& $refs[ 0 ];
            $refSubstr = null;
        }

        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return false;
        if ('' === $needle) {
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

        if ($status && $withSubstr) {
            $refSubstr = $fnSubstr($string, 0, $pos);
        }

        unset($refSubstr);

        return $status;
    }


    /**
     * > обрезает у строки подстроку с начала (ltrim, только для строк а не букв)
     */
    public function lcrop(string $string, string $needle, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $needle) return $string;
        if (0 === $limit) return $string;

        if ($limit < -1) {
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
            if (0 === $limit--) {
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
    public function rcrop(string $string, string $needle, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $needle) return $string;
        if (0 === $limit) return $string;

        if ($limit < -1) {
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
            if (0 === $limit--) {
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
    public function crop(string $string, $crops, bool $ignoreCase = null, $limits = null) : string
    {
        $_crops = Lib::php()->to_list($crops);
        $_limits = Lib::php()->to_list($limits ?? [ -1 ]);

        if (0 === count($_crops)) {
            return $string;
        }

        if (0 === count($_limits)) {
            throw new LogicException(
                'The `limits` should be array of integers or be null',
                $limits
            );
        }

        $needleLcrop = array_shift($_crops);
        $needleRcrop = (0 !== count($_crops))
            ? array_shift($_crops)
            : $needleLcrop;

        $limitLcrop = array_shift($_limits);
        $limitRcrop = (0 !== count($_limits))
            ? array_shift($_limits)
            : $limitLcrop;

        $result = $string;
        $result = $this->lcrop($result, $needleLcrop, $ignoreCase, $limitLcrop);
        $result = $this->rcrop($result, $needleRcrop, $ignoreCase, $limitRcrop);

        return $result;
    }


    /**
     * > добавляет подстроку в начало строки, если её уже там нет
     */
    public function unlcrop(string $string, string $needle, int $times = 1, bool $ignoreCase = null) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $needle) return $string;
        if (0 === $times) return $string;

        if ($times < 1) {
            throw new LogicException(
                'The `times` should be GTE 1',
                $times
            );
        }

        $result = $string;
        $result = $this->lcrop($result, $needle, $ignoreCase, -1);
        $result = str_repeat($needle, $times) . $result;

        return $result;
    }

    /**
     * > добавляет подстроку в конец строки, если её уже там нет
     */
    public function unrcrop(string $string, string $needle, int $times = 1, bool $ignoreCase = null) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $needle) return $string;
        if (0 === $times) return $string;

        if ($times < 1) {
            throw new LogicException(
                'The `times` should be GTE 1',
                $times
            );
        }

        $result = $string;
        $result = $this->rcrop($result, $needle, $ignoreCase, -1);
        $result = $result . str_repeat($needle, $times);

        return $result;
    }

    /**
     * > оборачивает строку в подстроки, если их уже там нет
     *
     * @param string|string[] $crops
     * @param int|int[]       $times
     */
    public function uncrop(string $string, $crops, $times = null, bool $ignoreCase = null) : string
    {
        $_crops = Lib::php()->to_list($crops);
        $_times = Lib::php()->to_list($times ?? [ 1 ]);

        if (0 === count($_crops)) {
            return $string;
        }

        if (0 === count($_times)) {
            throw new LogicException(
                'The `times` should be array of integers or be null',
                $times
            );
        }

        $needleLcrop = array_shift($_crops);
        $needleRcrop = (0 !== count($_crops))
            ? array_shift($_crops)
            : $needleLcrop;

        $timesLcrop = array_shift($_times);
        $timesRcrop = (0 !== count($_times))
            ? array_shift($_times)
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
        int $limit = null,
        int &$count = null
    )
    {
        $_search = Lib::php()->to_list($search);
        $_replace = Lib::php()->to_list($replace);
        $_subject = Lib::php()->to_list($subject);

        if (0 === count($_search)) {
            return $subject;
        }
        if (0 === count($_replace)) {
            return $subject;
        }
        if (0 === count($_subject)) {
            return [];
        }

        $_regexes = [];
        foreach ( $_search as $i => $s ) {
            $regex = preg_quote($s, '/');
            $regex = '/' . $regex . '/u';

            $_regexes[ $i ] = $regex;
        }

        $result = preg_replace($_regexes, $replace, $subject, $limit, $count);

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
        int $limit = null,
        int &$count = null
    )
    {
        $_search = Lib::php()->to_list($search);
        $_replace = Lib::php()->to_list($replace);
        $_subject = Lib::php()->to_list($subject);

        if (0 === count($_search)) {
            return $subject;
        }
        if (0 === count($_replace)) {
            return $subject;
        }
        if (0 === count($_subject)) {
            return [];
        }

        $_regexes = [];
        foreach ( $_search as $i => $s ) {
            $regex = preg_quote($s, '/');
            $regex = '/' . $regex . '/iu';

            $_regexes[ $i ] = $regex;
        }

        $result = preg_replace($_regexes, $replace, $subject, $limit, $count);

        return $result;
    }


    /**
     * @param string|string[] $lines
     */
    public function str_match(
        string $pattern, $lines,
        string $wildcardLetterSequence = null,
        string $wildcardSeparator = null,
        string $wildcardLetterSingle = null
    ) : array
    {
        if ('' === $pattern) {
            return [];
        }

        $_lines = Lib::php()->to_list($lines);
        if (0 === count($_lines)) {
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

        foreach ( $_lines as $i => $line ) {
            if (preg_match($regex, $line)) {
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
        string $wildcardLetterSequence = null,
        string $wildcardSeparator = null,
        string $wildcardLetterSingle = null
    ) : array
    {
        if ('' === $pattern) {
            return [];
        }

        $_lines = Lib::php()->to_list($lines);
        if (0 === count($_lines)) {
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

        foreach ( $_lines as $i => $line ) {
            if (preg_match($regex, $line)) {
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
        string $wildcardLetterSequence = null,
        string $wildcardSeparator = null,
        string $wildcardLetterSingle = null
    ) : array
    {
        if ('' === $pattern) {
            return [];
        }

        $_lines = Lib::php()->to_list($lines);
        if (0 === count($_lines)) {
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

        foreach ( $_lines as $i => $line ) {
            if (preg_match($regex, $line)) {
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
        string $wildcardLetterSequence = null,
        string $wildcardSeparator = null,
        string $wildcardLetterSingle = null
    ) : array
    {
        if ('' === $pattern) {
            return [];
        }

        $_lines = Lib::php()->to_list($lines);
        if (0 === count($_lines)) {
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

        foreach ( $_lines as $i => $line ) {
            if (preg_match($regex, $line)) {
                $result[] = $line;
            }
        }

        return $result;
    }

    protected function str_match_regex(
        string $pattern,
        string $wildcardLetterSequence = null,
        string $wildcardSeparator = null,
        string $wildcardLetterSingle = null
    ) : string
    {
        if ('' === $pattern) {
            return '';
        }

        $hasWildcardSeparator = (null !== $wildcardSeparator);
        $hasWildcardLetterSequence = (null !== $wildcardLetterSequence);
        $hasWildcardLetterSingle = (null !== $wildcardLetterSingle);

        $testUnique = [];
        if ($hasWildcardSeparator) {
            if (! $this->type_letter($_wildcardSeparator, $wildcardSeparator)) {
                throw new LogicException(
                    [ 'The `wildcardSeparator` should be null or exactly one letter', $wildcardSeparator ]
                );
            }

            $testUnique[] = $_wildcardSeparator;
        }
        if ($hasWildcardLetterSequence) {
            if (! $this->type_letter($_wildcardLetterSequence, $wildcardLetterSequence)) {
                throw new LogicException(
                    [ 'The `wildcardSequence` should be null or exactly one letter', $wildcardLetterSequence ]
                );
            }

            $testUnique[] = $_wildcardLetterSequence;
        }
        if ($hasWildcardLetterSingle) {
            if (! $this->type_letter($_wildcardLetterSingle, $wildcardLetterSingle)) {
                throw new LogicException(
                    [ 'The `wildcardLetter` should be null or exactly one letter', $wildcardLetterSingle ]
                );
            }

            $testUnique[] = $_wildcardLetterSingle;
        }

        if (count(array_unique($testUnique)) !== count($testUnique)) {
            throw new LogicException(
                [
                    'The wildcards should be different letters or nulls',
                    $wildcardSeparator,
                    $wildcardLetterSequence,
                    $wildcardLetterSingle,
                ]
            );
        }

        $thePreg = Lib::preg();

        $_pattern = $pattern;

        $anySymbolRegex = '.';

        $replacements = [];

        if ($hasWildcardSeparator) {
            $_wildcardSeparatorRegex = $thePreg->preg_quote_ord($_wildcardSeparator);

            $anySymbolRegex = '[^' . $_wildcardSeparatorRegex . ']';

            $replacement = '{{ 1 }}';
            $replacements[ preg_quote($replacement, '/') ] = $_wildcardSeparatorRegex;

            $_pattern = str_replace(
                $_wildcardSeparator,
                $replacement,
                $_pattern
            );
        }
        if ($hasWildcardLetterSequence) {
            $_wildcardLetterSequenceRegex = $thePreg->preg_quote_ord($_wildcardLetterSequence);

            $replacement = '{{ 2 }}';
            $replacements[ preg_quote($replacement, '/') ] = $anySymbolRegex . '+';

            $_pattern = str_replace(
                $_wildcardLetterSequence,
                $replacement,
                $_pattern
            );
        }
        if ($hasWildcardLetterSingle) {
            $_wildcardLetterSingleRegex = $thePreg->preg_quote_ord($_wildcardLetterSingle);

            $replacement = '{{ 3 }}';
            $replacements[ preg_quote($replacement, '/') ] = $anySymbolRegex;

            $_pattern = str_replace(
                $_wildcardLetterSingle,
                $replacement,
                $_pattern
            );
        }

        $_pattern = preg_quote($_pattern, '/');

        if (0 !== count($replacements)) {
            $_pattern = strtr($_pattern, $replacements);
        }

        $patternRegex = "/{$_pattern}/";

        if (false === preg_match($patternRegex, '')) {
            throw new RuntimeException(
                'Invalid regex for match: ' . $patternRegex
            );
        }

        return $_pattern;
    }


    /**
     * > 'theCamelCase'
     */
    public function camel(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d]+([\p{L}\d])/iu';

        $result = preg_replace_callback($regex, function ($m) {
            return $this->mb_func('strtoupper')($m[ 1 ]);
        }, $result);

        $result = $this->lcfirst($result);

        return $result;
    }

    /**
     * > 'ThePascalCase'
     */
    public function pascal(string $string) : string
    {
        if ('' === $string) return '';

        $result = $string;

        $regex = '/[^\p{L}\d]+([\p{L}\d])/iu';

        $result = preg_replace_callback($regex, function ($m) {
            return $this->mb_func('strtoupper')($m[ 1 ]);
        }, $result);

        $result = $this->ucfirst($result);

        return $result;
    }


    /**
     * > 'the Space case'
     */
    public function space(string $string) : string
    {
        if ('' === $string) return '';

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
        if ('' === $string) return '';

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
        if ('' === $string) return '';

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
    public function translit_ru2ascii(string $string, string $delimiter = null, $ignoreSymbols = null) : string
    {
        $delimiter = $delimiter ?? '-';

        $theMb = Lib::mb();
        $thePreg = Lib::preg();

        $dictionary = [
            'а' => 'a',
            'б' => '6',
            'в' => 'B',
            'г' => 'r',
            'д' => 'g',
            'е' => 'e',
            'ж' => '}|{',
            'з' => '3',
            'и' => 'u',
            'й' => 'u',
            'к' => 'K',
            'л' => 'JI',
            'м' => 'M',
            'н' => 'H',
            'о' => 'o',
            'п' => 'n',
            'р' => 'p',
            'с' => 'c',
            'т' => 'T',
            'у' => 'y',
            'ф' => '(|)',
            'х' => 'x',
            'ц' => 'll,',
            'ч' => '4',
            'ш' => 'lll',
            'щ' => 'lll,',
            'ъ' => '\'b',
            'ы' => 'bI',
            'ь' => 'b',
            'э' => 'e',
            'ю' => 'I0',
            'я' => '9I',
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

        if (isset($dictionary[ $delimiter ])) {
            throw new LogicException(
                [ 'The `delimiter` should not be in dictionary', $delimiter ]
            );
        }

        $_ignoreSymbols = is_array($ignoreSymbols)
            ? $ignoreSymbols
            : ($ignoreSymbols ? [ $ignoreSymbols ] : []);

        $ignoreSymbolsRegex = [];
        foreach ( $_ignoreSymbols as $i => $symbols ) {
            if (is_string($i)) {
                $symbols = $i;
            }

            $_symbols = (string) $symbols;
            $_symbols = $theMb->str_split($_symbols, 1);

            foreach ( $_symbols as $symbol ) {
                if (isset($dictionary[ $symbol ])) {
                    throw new LogicException(
                        [ 'Each of `ignoreSymbols` should not be in dictionary', $symbol ]
                    );
                }

                $symbol = mb_strtolower($symbol);

                $ignoreSymbolsRegex[ $symbol ] = true;
            }
        }

        $ignoreSymbolsRegex = array_keys($ignoreSymbolsRegex);
        $ignoreSymbolsRegex = implode('', $ignoreSymbolsRegex);
        $ignoreSymbolsRegex = $thePreg->preg_quote_ord($ignoreSymbolsRegex);

        $result = mb_strtolower($string);

        $result = preg_replace("/[^а-яё0-9{$ignoreSymbolsRegex} ]/iu", $delimiter, $result);

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
    public function trim_it($strings, string $characters = null) : \Generator
    {
        $_characters = $characters ?? " \n\r\t\v\0";

        $_strings = is_iterable($strings)
            ? $strings
            : (is_string($strings) ? [ $strings ] : []);

        foreach ( $_strings as $string ) {
            yield trim($string, $_characters);
        }
    }

    /**
     * > обычный трим завернутый в генератор
     *
     * @return \Generator<string>
     */
    public function ltrim_it($strings, string $characters = null) : \Generator
    {
        $_characters = $characters ?? " \n\r\t\v\0";

        $_strings = is_iterable($strings)
            ? $strings
            : (is_string($strings) ? [ $strings ] : []);

        foreach ( $_strings as $string ) {
            yield ltrim($string, $_characters);
        }
    }

    /**
     * > обычный трим завернутый в генератор
     *
     * @return \Generator<string>
     */
    public function rtrim_it($strings, string $characters = null) : \Generator
    {
        $_characters = $characters ?? " \n\r\t\v\0";

        $_strings = is_iterable($strings)
            ? $strings
            : (is_string($strings) ? [ $strings ] : []);

        foreach ( $_strings as $string ) {
            yield rtrim($string, $_characters);
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
    public function prefix(string $string, int $length = null) : string
    {
        if ('' === $string) {
            return '';
        }

        $length = $length ?? 3;

        if ($length < 1) {
            throw new LogicException(
                [ 'The `length` should be greater than zero', $length ]
            );
        }

        $theStr = Lib::str();

        $isUnicodeAllowed = $theStr->static_mbstring();

        $_string = $isUnicodeAllowed
            ? preg_replace('/(?:[^\w]|[_])+/u', '', $string)
            : preg_replace('/(?:[^\w]|[_])+/', '', $string);

        if ('' === $_string) {
            throw new LogicException(
                [ 'The `string` should contain at least one letter', $string ]
            );
        }

        $fnStrlen = $theStr->mb_func('strlen');
        $fnSubstr = $theStr->mb_func('substr');

        $source = $_string;
        $sourceLen = $fnStrlen($source);

        $_length = min($length, $sourceLen);

        if (0 === $_length) {
            return '';
        }

        $vowels = '';
        $vowelsArray = $this->loadVowels();
        foreach ( $vowelsArray as $letter => $index ) {
            $vowels .= implode('', array_keys($index));
        }

        $sourceConsonants = [];
        $sourceVowels = [];
        for ( $i = 0; $i < $sourceLen; $i++ ) {
            $letter = $fnSubstr($source, $i, 1);

            ('' === trim($letter, $vowels))
                ? ($sourceVowels[ $i ] = $letter)
                : ($sourceConsonants[] = $letter);
        }

        $letters = [];

        $hasVowel = false;
        $left = $_length;
        for ( $i = 0; $i < $_length; $i++ ) {
            $letter = null;
            if (isset($sourceVowels[ $i ])) {
                if (! $hasVowel) {
                    $letter = $sourceVowels[ $i ];
                    $hasVowel = true;

                } elseif ($left > count($sourceConsonants)) {
                    $letter = $sourceVowels[ $i ];
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
            $line = rtrim($line, "\r\n");

            $lines[ $i ] = $line;
        }

        return $lines;
    }

    public function eol(string $text, $eol = null, array $refs = []) : string
    {
        $withLines = array_key_exists(0, $refs);

        $refLines = null;

        if ($withLines) {
            $refLines =& $refs[ 0 ];
            $refLines = null;
        }

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
    public function utf8_encode(string $string, string $encoding = null) : ?string
    {
        if (! \function_exists('iconv')) {
            throw new RuntimeException(
                'Unable to convert a non-UTF-8 string to UTF-8: required function iconv() does not exist. You should install ext-iconv or symfony/polyfill-iconv.'
            );
        }

        $_encoding = null
            ?? $encoding
            ?? (\ini_get('php.output_encoding') ?: null)
            ?? (\ini_get('default_charset') ?: null)
            ?? 'UTF-8';

        $stringConverted = @iconv($_encoding, 'UTF-8', $string);
        if (false !== $stringConverted) {
            return $stringConverted;
        }

        if ('CP1252' !== $_encoding) {
            $stringConverted = @iconv('CP1252', 'UTF-8', $string);
            if (false !== $stringConverted) {
                return $stringConverted;
            }
        }

        $stringConverted = @iconv('CP850', 'UTF-8', $string);
        if (false !== $stringConverted) {
            return $stringConverted;
        }

        return null;
    }

    /**
     * > конвертирует непечатаемые символы строки в байтовые и hex последовательности, которые можно прочесть
     */
    public function dump_encode(string $string, string $encoding = null) : ?string
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
        if ($count) {
            $foundBinary = true;
        }

        if ($isUtf8 = $this->is_utf8($result)) {
            $invisibles = $this->loadInvisibles();

            $count = 0;

            $result = str_replace(
                array_keys($invisibles),
                array_values($invisibles),
                $result,
                $count
            );

            if ($count) {
                $foundBinary = true;
            }

        } else {
            $_varUtf8 = $this->utf8_encode($result, $encoding);

            if ($_varUtf8 !== $result) {
                $result = $_varUtf8;

                $foundBinary = true;
            }
        }

        if ($foundBinary) {
            $result = "b`{$result}`";
        }

        foreach ( $asciiControlsOnlyTrims as $i => $v ) {
            if ($i === "\n") {
                $asciiControlsOnlyTrims[ $i ] .= $i;
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
