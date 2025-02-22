<?php
/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
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

        $this->inflector = new Inflector();
        $this->interpolator = new Interpolator();
        $this->slugger = new Slugger();

        $this->mbMode = $mbMode;
    }


    public function inflector_static(InflectorInterface $inflector = null) : InflectorInterface
    {
        if (null !== $inflector) {
            $last = $this->inflector;

            $current = $inflector;

            $this->inflector = $current;

            $result = $last;
        }

        $result = $result ?? $this->inflector;

        return $result;
    }

    public function inflector() : InflectorInterface
    {
        return $this->inflector_static();
    }


    public function interpolator_static(InterpolatorInterface $interpolator = null) : InterpolatorInterface
    {
        if (null !== $interpolator) {
            $last = $this->interpolator;

            $current = $interpolator;

            $this->interpolator = $current;

            $result = $last;
        }

        $result = $result ?? $this->interpolator;

        return $result;
    }

    public function interpolator() : InterpolatorInterface
    {
        return $this->interpolator_static();
    }


    public function slugger_static(SluggerInterface $slugger = null) : SluggerInterface
    {
        if (null !== $slugger) {
            $last = $this->slugger;

            $current = $slugger;

            $this->slugger = $current;

            $result = $last;
        }

        $result = $result ?? $this->slugger;

        return $result;
    }

    public function slugger() : SluggerInterface
    {
        return $this->slugger_static();
    }


    public function mb_mode_static(bool $mbMode = null) : bool
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
     * @param callable|callable-string|null $fn
     */
    public function mb(string $fn = null, ...$args)
    {
        if (null === $fn) {
            $result = $this->mb_mode_static();

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
        if (! $this->mb_mode_static()) {
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


    /**
     * @return array
     */
    public function loadAccents() : array
    {
        $list = [
            '' => '£',

            'a' => 'àáâãāăȧảǎȁąạḁẚầấẫẩằắẵẳǡǟǻậặǽǣ',
            'A' => 'ÀÁÂÃĀĂȦẢǍȀĄẠḀAʾẦẤẪẨẰẮẴẲǠǞǺẬẶǼǢ',

            'aa' => 'å',
            'Aa' => 'Å',

            'ae' => 'äæ',
            'Ae' => 'ÄÆ',

            'c' => 'çćĉċč',
            'C' => 'ÇĆĈĊČ',

            'd' => 'ďđ',
            'D' => 'ĎĐ',

            'e' => 'èéêẽēĕėëẻěȅȇẹȩęḙḛềếễểḕḗệḝёє',
            'E' => 'ÈÉÊẼĒĔĖËẺĚȄȆẸȨĘḘḚỀẾỄỂḔḖỆḜЁЄ€',

            'g' => 'ĝğġģ',
            'G' => 'ĜĞĠĢ',

            'h' => 'ĥħ',
            'H' => 'ĤĦ',

            'i' => 'ìíîĩīĭïỉǐịįȉȋḭḯї',
            'I' => 'ÌÍÎĨĪĬÏỈǏỊĮȈȊḬḮЇ',

            'ij' => 'ĳ',
            'IJ' => 'Ĳ',

            'j' => 'ĵ',
            'J' => 'Ĵ',

            'k' => 'ķĸ',
            'K' => 'Ķ',

            'l' => 'ĺļľŀł',
            'L' => 'ĹĻĽĿŁ',

            'n' => 'ñńņňŊ',
            'N' => 'ÑŃŅŇŉŋ',

            'o' => 'òóôõōŏȯỏőǒȍȏơǫọøồốỗổȱȫȭṍṏṑṓờớỡởợǭộǿ',
            'O' => 'ÒÓÔÕŌŎȮỎŐǑȌȎƠǪỌØỒỐỖỔȰȪȬṌṎṐṒỜỚỠỞỢǬỘǾ',

            'oe' => 'öœ',
            'Oe' => 'Ö',
            'OE' => 'Œ',

            'r' => 'ŕŗř',
            'R' => 'ŔŖŘ',

            's' => 'śŝşšſ',
            'S' => 'ŚŜŞŠ',

            'ss' => 'ß',

            't' => 'ţťŧ',
            'T' => 'ŢŤŦ',

            'u' => 'ùúûũūŭủůűǔȕȗưụṳųṷṵṹṻǖǜǘǖǚừứữửựў',
            'U' => 'ÙÚÛŨŪŬỦŮŰǓȔȖƯỤṲŲṶṴṸṺǕǛǗǕǙỪỨỮỬỰЎ',

            'ue' => 'ü',
            'Ue' => 'Ü',

            'w' => 'ŵ',
            'W' => 'Ŵ',

            'y' => 'ýÿŷ',
            'Y' => 'ÝŶŸ',

            'z' => 'źżž',
            'Z' => 'ŹŻŽ',
        ];

        return $list;
    }

    /**
     * @return array
     */
    public function loadVowels() : array
    {
        $list = [
            'a' => 'aàáâãāăȧäảåǎȁąạḁẚầấẫẩằắẵẳǡǟǻậặæǽǣая',
            'A' => 'AÀÁÂÃĀĂȦÄẢÅǍȀĄẠḀAʾẦẤẪẨẰẮẴẲǠǞǺẬẶÆǼǢАЯ',

            'e' => 'eèéêẽēĕėëẻěȅȇẹȩęḙḛềếễểḕḗệḝеёє',
            'E' => 'EÈÉÊẼĒĔĖËẺĚȄȆẸȨĘḘḚỀẾỄỂḔḖỆḜЕЁЄ€',

            'i' => 'iìíîĩīĭïỉǐịįȉȋḭḯиыії',
            'I' => 'IÌÍÎĨĪĬÏỈǏỊĮȈȊḬḮИЫІЇ',

            'o' => 'oòóôõōŏȯöỏőǒȍȏơǫọøồốỗổȱȫȭṍṏṑṓờớỡởợǭộǿœо',
            'O' => 'OÒÓÔÕŌŎȮÖỎŐǑȌȎƠǪỌØỒỐỖỔȰȪȬṌṎṐṒỜỚỠỞỢǬỘǾŒО',

            'u' => 'uùúûũūŭüủůűǔȕȗưụṳųṷṵṹṻǖǜǘǖǚừứữửựуюў',
            'U' => 'UÙÚÛŨŪŬÜỦŮŰǓȔȖƯỤṲŲṶṴṸṺǕǛǗǕǙỪỨỮỬỰУЮЎ',
        ];

        return $list;
    }

    /**
     * @return array
     */
    public function loadTrims() : array
    {
        $list = [
            chr(9)  => '\t',   // "\x09" // TAB (Horizontal Tab)  (ASCII 9)
            chr(10) => '\n',   // "\x0A" // LF  (Line Feed)       (ASCII 10)
            chr(11) => '\v',   // "\x0B" // VT  (Vertical Tab)    (ASCII 11)
            chr(13) => '\r',   // "\x0D" // CR  (Carriage Return) (ASCII 13)
        ];

        return $list;
    }

    /**
     * @return array
     */
    public function loadAsciiControls() : array
    {
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

        return $list;
    }

    /**
     * @return array
     */
    public function loadAsciiControlsNoTrims() : array
    {
        $list = [
            // chr(9)  => '\t',   // "\x09" // TAB (Horizontal Tab)  (ASCII 9)
            // chr(10) => '\n',   // "\x0A" // LF  (Line Feed)       (ASCII 10)
            // chr(11) => '\v',   // "\x0B" // VT  (Vertical Tab)    (ASCII 11)
            // chr(13) => '\r',   // "\x0D" // CR  (Carriage Return) (ASCII 13)
            //
            chr(0)  => '\0',   // "\0"   // NULL (ASCII 0)
            chr(1)  => '\x01', // "\x01" // SOH (Start of Heading) (ASCII 1)
            chr(2)  => '\x02', // "\x02" // STX (Start of Text)   (ASCII 2)
            chr(3)  => '\x03', // "\x03" // ETX (End of Text)     (ASCII 3)
            chr(4)  => '\x04', // "\x04" // EOT (End of Transmission) (ASCII 4)
            chr(5)  => '\x05', // "\x05" // ENQ (Enquiry)         (ASCII 5)
            chr(6)  => '\x06', // "\x06" // ACK (Acknowledge)     (ASCII 6)
            chr(7)  => '\a',   // "\x07" // BEL (Bell)            (ASCII 7)
            chr(8)  => '\b',   // "\x08" // BS  (Backspace)       (ASCII 8)
            chr(12) => '\f',   // "\x0C" // FF  (Form Feed)       (ASCII 12)
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

        return $list;
    }

    /**
     * @return array
     */
    public function loadInvisibles() : array
    {
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
        return preg_match('//u', $str) === 1;
    }


    public function utf8_encode(string $string) : ?string
    {
        if (! \function_exists('iconv')) {
            throw new RuntimeException(
                'Unable to convert a non-UTF-8 string to UTF-8: required function iconv() does not exist. You should install ext-iconv or symfony/polyfill-iconv.'
            );
        }

        $charset = null
            ?? (\ini_get('php.output_encoding') ?: null)
            ?? (\ini_get('default_charset') ?: null)
            ?? 'UTF-8';

        $stringConverted = @iconv($charset, 'UTF-8', $string);
        if (false !== $stringConverted) {
            return $stringConverted;
        }

        if (true
            && ('CP1252' !== $charset)
            && false !== $stringConverted = @iconv('CP1252', 'UTF-8', $string)
        ) {
            return $stringConverted;
        }

        $stringConverted = iconv('CP850', 'UTF-8', $string);

        return $stringConverted;
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

        $len = $this->mb_mode_static()
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
        if ($this->mb_mode_static()) {
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
        if ($this->mb_mode_static()) {
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
        if ($this->mb_mode_static()) {
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
        if ($this->mb_mode_static()) {
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

        $vowels = implode('', $this->loadVowels());

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
