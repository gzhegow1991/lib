<?php
/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
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
    protected $mbMode = false;


    public function __construct()
    {
        $mbMode = extension_loaded('mbstring');

        $this->mbMode = $mbMode;
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


    public function static_mb_mode(bool $mbMode = null) : bool
    {
        if (null !== $mbMode) {
            if ($mbMode) {
                if (! extension_loaded('mbstring')) {
                    throw new RuntimeException(
                        'Unable to enable `mb_mode` due to `mbstring` extension is missing'
                    );
                }
            }

            $last = $this->mbMode;

            $current = $mbMode;

            $this->mbMode = $current;

            $result = $last;
        }

        $result = $result ?? $this->mbMode;

        return $result;
    }


    /**
     * @param string|null $result
     */
    public function type_trim(&$result, $value, string $characters = null) : bool
    {
        $result = null;

        $characters = $characters ?? " \n\r\t\v\0";

        if (! Lib::type()->string($_value, $value)) {
            return false;
        }

        $_value = trim($_value, $characters);

        if ('' === $_value) {
            return false;
        }

        $result = $_value;

        return $_value;
    }


    /**
     * @param string|null $result
     */
    public function type_letter(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (1 !== $this->strlen($_value)) {
            return false;
        }

        $result = $_value;

        return $_value;
    }


    /**
     * @param callable|callable-string|null $fn
     */
    public function mb(string $fn = null, ...$args)
    {
        if (null === $fn) {
            $result = $this->static_mb_mode();

        } else {
            $_fn = $this->mb_func($fn);

            $result = $_fn(...$args);
        }

        return $result;
    }

    /**
     * @param callable|callable-string $fn
     *
     * @return callable
     */
    public function mb_func(string $fn)
    {
        if (! $this->static_mb_mode()) {
            return $fn;
        }

        $result = null;

        switch ( $fn ):
            case 'str_split':
                $result = (PHP_VERSION_ID >= 74000)
                    ? 'mb_str_split'
                    : [ Lib::mb(), 'str_split' ];

                break;

            default:
                $result = 'mb_' . $fn;

                break;

        endswitch;

        return $result;
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
        $list = $this->loadAsciiControlsOnlyTrims($hex);

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


    public function is_binary(string $str) : bool
    {
        for ( $i = 0; $i < strlen($str); $i++ ) {
            $chr = $str[ $i ];
            $ord = ord($chr);

            if ($ord > 127) {
                return true;
            }
        }

        return false;
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
        $_string = $string;

        $asciiControlsNoTrims = $this->loadAsciiControlsNoTrims(true);
        $asciiControlsOnlyTrims = $this->loadAsciiControlsOnlyTrims(false);

        $foundBinary = false;
        $count = 0;
        $_string = str_replace(
            array_keys($asciiControlsNoTrims),
            array_values($asciiControlsNoTrims),
            $_string,
            $count
        );
        if ($count) {
            $foundBinary = true;
        }

        if ($isUtf8 = $this->is_utf8($_string)) {
            $invisibles = $this->loadInvisibles();

            $count = 0;

            $_string = str_replace(
                array_keys($invisibles),
                array_values($invisibles),
                $_string,
                $count
            );

            if ($count) {
                $foundBinary = true;
            }

        } else {
            $_varUtf8 = $this->utf8_encode($_string, $encoding);

            if ($_varUtf8 !== $_string) {
                $_string = $_varUtf8;

                $foundBinary = true;
            }
        }

        if ($foundBinary) {
            $_string = "b`{$_string}`";
        }

        foreach ( $asciiControlsOnlyTrims as $i => $v ) {
            if ($i === "\n") {
                $asciiControlsOnlyTrims[ $i ] .= $i;
            }
        }
        $_string = str_replace(
            array_keys($asciiControlsOnlyTrims),
            array_values($asciiControlsOnlyTrims),
            $_string
        );

        return $_string;
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

    public function eol(string $text, $eol = null, array &$lines = null) : string
    {
        $lines = null;

        $_eol = $eol ?? "\n";
        $_eol = (string) $_eol;

        $lines = $this->lines($text);

        $output = implode($_eol, $lines);

        return $output;
    }


    /**
     * > возвращает число символов в строке
     */
    public function strlen($value) : int
    {
        if (! is_string($value)) {
            return 0;
        }

        if ('' === $value) {
            return 0;
        }

        $len = $this->static_mb_mode()
            ? mb_strlen($value)
            : count(preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY));

        return $len;
    }

    /**
     * > возвращает размер строки в байтах
     */
    public function strsize($value) : int
    {
        if (! is_string($value)) {
            return 0;
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
        if ($this->static_mb_mode()) {
            $mbEncodingArgs = [];
            if (null !== $mb_encoding) {
                $mbEncodingArgs[] = $mb_encoding;
            }

            $result = mb_strtolower($string, ...$mbEncodingArgs);

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
        if ($this->static_mb_mode()) {
            $mbEncodingArgs = [];
            if (null !== $mb_encoding) {
                $mbEncodingArgs[] = $mb_encoding;
            }

            $result = mb_strtoupper($string, ...$mbEncodingArgs);

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
        if ($this->static_mb_mode()) {
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
        if ($this->static_mb_mode()) {
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
        $regex = '/(^|[' . preg_quote($separators, '/') . '])(\w)/u';

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
        $regex = '/(^|[' . preg_quote($separators, '/') . '])(\w)/u';

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


    /**
     * > если строка начинается на искомую, отрезает ее и возвращает укороченную
     * if (null !== ($substr = _str_starts('hello', 'h'))) {} // 'ello'
     */
    public function starts(string $string, string $needle, bool $ignoreCase = null) : ?string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return null;
        if ('' === $needle) return $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

        $pos = $fnStrpos($string, $needle);

        $result = 0 === $pos
            ? $fnSubstr($string, $fnStrlen($needle))
            : null;

        return $result;
    }

    /**
     * > если строка заканчивается на искомую, отрезает ее и возвращает укороченную
     * if (null !== ($substr = _str_ends('hello', 'o'))) {} // 'hell'
     */
    public function ends(string $string, string $needle, bool $ignoreCase = null) : ?string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return null;
        if ('' === $needle) return $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrrpos = $ignoreCase
            ? $this->mb_func('strripos')
            : $this->mb_func('strrpos');

        $pos = $fnStrrpos($string, $needle);

        $result = $pos === $fnStrlen($string) - $fnStrlen($needle)
            ? $fnSubstr($string, 0, $pos)
            : null;

        return $result;
    }

    /**
     * > ищет подстроку в строке и разбивает по ней результат
     */
    public function contains(string $string, string $needle, bool $ignoreCase = null, int $limit = null) : array
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return [];
        if ('' === $needle) return [ $string ];

        $strCase = $ignoreCase
            ? str_ireplace($needle, $needle, $string)
            : $string;

        $result = [];

        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

        if (false !== $fnStrpos($strCase, $needle)) {
            $result = null
                ?? (isset($limit) ? explode($needle, $strCase, $limit) : null)
                ?? (explode($needle, $strCase));
        }

        return $result;
    }


    /**
     * > обрезает у строки подстроку с начала (ltrim, только для строк а не букв)
     */
    public function lcrop(string $string, string $lcrop, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $lcrop) return $string;

        $result = $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrpos = $ignoreCase
            ? $this->mb_func('stripos')
            : $this->mb_func('strpos');

        $pos = $fnStrpos($result, $lcrop);

        while ( $pos === 0 ) {
            if (! $limit--) {
                break;
            }

            $result = $fnSubstr($result,
                $fnStrlen($lcrop)
            );

            $pos = $fnStrpos($result, $lcrop);
        }

        return $result;
    }

    /**
     * > обрезает у строки подстроку с конца (rtrim, только для строк а не букв)
     */
    public function rcrop(string $string, string $rcrop, bool $ignoreCase = null, int $limit = -1) : string
    {
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $string) return $string;
        if ('' === $rcrop) return $string;

        $result = $string;

        $fnStrlen = $this->mb_func('strlen');
        $fnSubstr = $this->mb_func('substr');
        $fnStrrpos = $ignoreCase
            ? $this->mb_func('strripos')
            : $this->mb_func('strrpos');


        $pos = $fnStrrpos($result, $rcrop);

        while ( $pos === ($fnStrlen($result) - $fnStrlen($rcrop)) ) {
            if (! $limit--) {
                break;
            }

            $result = $fnSubstr($result, 0, $pos);

            $pos = $fnStrrpos($result, $rcrop);
        }

        return $result;
    }

    /**
     * > обрезает у строки подстроки с обеих сторон (trim, только для строк а не букв)
     */
    public function crop(string $string, $crops, bool $ignoreCase = null, int $limit = -1) : string
    {
        $crops = is_array($crops)
            ? $crops
            : ($crops ? [ $crops ] : []);

        if (! $crops) {
            return $string;
        }

        $needleRcrop = $needleLcrop = array_shift($crops);

        if ($crops) $needleRcrop = array_shift($crops);

        $result = $string;
        $result = $this->lcrop($result, $needleLcrop, $ignoreCase, $limit);
        $result = $this->rcrop($result, $needleRcrop, $ignoreCase, $limit);

        return $result;
    }


    /**
     * > добавляет подстроку в начало строки, если её уже там нет
     */
    public function unlcrop(string $string, string $lcrop, int $times = null, bool $ignoreCase = null) : string
    {
        $times = $times ?? 1;
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $lcrop) return $string;
        if ($times < 1) $times = 1;

        $result = $string;
        $result = $this->lcrop($result, $lcrop, $ignoreCase);
        $result = str_repeat($lcrop, $times) . $result;

        return $result;
    }

    /**
     * > добавляет подстроку в конец строки, если её уже там нет
     */
    public function unrcrop(string $string, string $rcrop, int $times = null, bool $ignoreCase = null) : string
    {
        $times = $times ?? 1;
        $ignoreCase = $ignoreCase ?? true;

        if ('' === $rcrop) return $string;
        if ($times < 1) $times = 1;

        $result = $string;
        $result = $this->rcrop($result, $rcrop, $ignoreCase);
        $result = $result . str_repeat($rcrop, $times);

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
        $times = $times ?? 1;

        $_crops = (array) $crops;
        $_times = (array) $times;

        if (! $_crops) {
            return $string;
        }

        $result = $string;
        $result = $this->unlcrop($result, $_crops[ 0 ], $_times[ 0 ], $ignoreCase);
        $result = $this->unrcrop($result, $_crops[ 1 ] ?? $_crops[ 0 ], $_times[ 1 ] ?? $_times[ 0 ], $ignoreCase);

        return $result;
    }


    /**
     * > str_replace с поддержкой limit замен
     */
    public function replace_limit(
        $search, $replace, $subject, int $limit = null,
        int &$count = null
    ) : string
    {
        $count = null;

        if ((null !== $limit) && ($limit <= 0)) {
            return $subject;

        } elseif (! isset($limit)) {
            $result = str_replace($search, $replace, $subject, $count);

            return $result;
        }

        $occurrences = substr_count($subject, $search);

        if ($occurrences === 0) {
            return $subject;

        } elseif ($occurrences <= $limit) {
            $result = str_replace($search, $replace, $subject, $count);

            return $result;
        }

        $position = 0;
        for ( $i = 0; $i < $limit; $i++ ) {
            $position = strpos($subject, $search, $position) + strlen($search);
        }

        $substring = substr($subject, 0, $position + 1);

        $substring = str_replace($search, $replace, $substring, $count);

        $result = substr_replace($subject, $substring, 0, $position + 1);

        return $result;
    }


    public function match(
        string $pattern, $lines,
        string $wildcardSequence = null,
        string $wildcardSeparator = null,
        string $wildcardLetter = null
    ) : array
    {
        if ('' === $pattern) {
            return [];
        }

        $_pattern = $pattern;
        $_lines = Lib::php()->to_list($lines);

        $anySymbolRegex = '.';

        $hasWildcardSeparator = (null !== $wildcardSeparator);
        $hasWildcardSequence = (null !== $wildcardSequence);
        $hasWildcardLetter = (null !== $wildcardLetter);

        $testUnique = [];

        if ($hasWildcardSeparator) {
            if (! $this->type_letter($_wildcardSeparator, $wildcardSeparator)) {
                throw new LogicException(
                    [ 'The `wildcardSeparator` should be null or exactly one letter', $wildcardSeparator ]
                );
            }

            $testUnique[] = $_wildcardSeparator;
        }

        if ($hasWildcardSequence) {
            if (! $this->type_letter($_wildcardSequence, $wildcardSequence)) {
                throw new LogicException(
                    [ 'The `wildcardSequence` should be null or exactly one letter', $wildcardSequence ]
                );
            }

            $testUnique[] = $_wildcardSequence;
        }
        if ($hasWildcardLetter) {
            if (! $this->type_letter($_wildcardLetter, $wildcardLetter)) {
                throw new LogicException(
                    [ 'The `wildcardLetter` should be null or exactly one letter', $wildcardLetter ]
                );
            }

            $testUnique[] = $_wildcardLetter;
        }

        if (count(array_unique($testUnique)) !== count($testUnique)) {
            throw new LogicException(
                [
                    'The wildcards should be different letters or nulls',
                    $wildcardSeparator,
                    $wildcardSequence,
                    $wildcardLetter,
                ]
            );
        }

        $_pattern = preg_quote($_pattern, '/');

        if ($hasWildcardSeparator) {
            $_wildcardSeparatorRegex = preg_quote($_wildcardSeparator, '/');

            $anySymbolRegex = '[^' . $_wildcardSeparatorRegex . ']';
        }
        if ($hasWildcardSequence) {
            $_wildcardSequenceRegex = preg_quote($_wildcardSequence, '/');

            $_pattern = str_replace('\\' . $_wildcardSequence, $anySymbolRegex . '+', $_pattern);
        }
        if ($hasWildcardLetter) {
            $_wildcardLetterRegex = preg_quote($_wildcardLetter, '/');

            $_pattern = str_replace('\\' . $_wildcardLetter, $anySymbolRegex, $_pattern);
        }

        $patternRegex = "/^{$_pattern}$/u";

        if (false === preg_match($patternRegex, '')) {
            throw new RuntimeException(
                'Invalid regex for match: ' . $patternRegex
            );
        }

        $result = [];

        foreach ( $_lines as $i => $line ) {
            if (preg_match($patternRegex, $line)) {
                $result[] = $line;
            }
        }

        return $result;
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
        $ignoreSymbolsRegex = implode('', array_keys($ignoreSymbolsRegex));
        $ignoreSymbolsRegex = preg_quote($ignoreSymbolsRegex, '/');

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
     * gzhegow, урезает английское слово до префикса из нескольких букв - когда имя индекса в бд слишком длинное
     */
    public function prefix(string $string, int $len = null) : string
    {
        if ('' === $string) {
            return '';
        }

        $theMb = Lib::mb();

        $len = $len ?? 3;
        $len = max(0, $len);

        $source = preg_replace('/(?:[^\w]|[_])+/u', '', $string);
        $sourceLen = mb_strlen($source);

        $len = min($len, $sourceLen);

        if (0 === $len) {
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
            $letter = mb_substr($source, $i, 1);

            ('' === trim($letter, $vowels))
                ? ($sourceVowels[ $i ] = $letter)
                : ($sourceConsonants[] = $letter);
        }

        $letters = [];

        $hasVowel = false;
        $left = $len;
        for ( $i = 0; $i < $len; $i++ ) {
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
}
