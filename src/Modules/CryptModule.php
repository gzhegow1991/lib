<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Crypt\Alphabet;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class CryptModule
{
    const ALPHABET_BASE_2  = '01';
    const ALPHABET_BASE_4  = '0123';
    const ALPHABET_BASE_8  = '01234567';
    const ALPHABET_BASE_16 = '0123456789ABCDEF';

    const ALPHABET_BASE_32 = '0123456789ABCDEFGHIJKLMNOPQRSTUV';
    const ALPHABET_BASE_36 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const ALPHABET_BASE_62 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const ALPHABET_BASE_64 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';

    const ALPHABET_BASE_58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    const ALPHABET_BASE_32_RFC4648         = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    const ALPHABET_BASE_32_RFC4648_HEXLIKE = self::ALPHABET_BASE_32;
    const ALPHABET_BASE_64_RFC4648         = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    const ALPHABET_BASE_64_RFC4648_URLSAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

    const LIST_ALPHABET = [
        self::ALPHABET_BASE_2                  => true,
        self::ALPHABET_BASE_4                  => true,
        self::ALPHABET_BASE_8                  => true,
        self::ALPHABET_BASE_16                 => true,
        //
        self::ALPHABET_BASE_32                 => true,
        self::ALPHABET_BASE_36                 => true,
        self::ALPHABET_BASE_62                 => true,
        self::ALPHABET_BASE_64                 => true,
        //
        self::ALPHABET_BASE_58                 => true,
        //
        self::ALPHABET_BASE_32_RFC4648         => true,
        // self::ALPHABET_BASE_32_RFC4648_HEXLIKE => true,
        self::ALPHABET_BASE_64_RFC4648         => true,
        self::ALPHABET_BASE_64_RFC4648_URLSAFE => true,
    ];


    /**
     * @param Alphabet|null $result
     */
    public function type_alphabet(&$result, $value) : bool
    {
        $result = null;

        $theType = Lib::type();
        $theMb = Lib::mb();

        if (! $theType->string_not_empty($_value, $value)) {
            return false;
        }

        preg_replace('/\s+/', '', $_value, 1, $count);
        if ($count > 0) {
            return false;
        }

        $len = mb_strlen($_value);
        if (mb_strlen($_value) <= 1) {
            return false;
        }

        $seen = [];
        $regex = '/[';
        $regexNot = '/[^';
        for ( $i = 0; $i < $len; $i++ ) {
            $letter = mb_substr($_value, $i, 1);

            if (isset($seen[ $letter ])) {
                return false;
            }
            $seen[ $letter ] = true;

            $letterRegex = sprintf('\x{%X}', mb_ord($letter));

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


    /**
     * @param string|null $result
     */
    public function type_base(&$result, $value, $alphabet) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (! $this->type_alphabet($_alphabet, $alphabet)) {
            return false;
        }

        if (preg_match($_alphabet->getRegexNot(), $_value, $m)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_base_bin(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (preg_match('~[^01]~', $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_base_oct(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (preg_match('~[^01234567]~', $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_base_dec(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (preg_match('~[^0123456789]~', $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null $result
     */
    public function type_base_hex(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (preg_match('~[^0123456789ABCDEF]~', $_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }


    public function hash(
        string $algo,
        string $datastring,
        bool $binary = null, array $options = []
    ) : string
    {
        $binary = $binary ?? false;

        $result = null;

        $result = (PHP_VERSION_ID >= 80100)
            ? hash($algo, $datastring, $binary, $options)
            : hash($algo, $datastring, $binary);

        return $result;
    }

    public function hash_equals(
        string $user_hash,
        string $algo,
        string $user_datastring,
        bool $binary = null, array $options = []
    )
    {
        $known_hash = $this->hash(
            $algo,
            $user_datastring,
            $binary, $options
        );

        return hash_equals($known_hash, $user_hash);
    }

    public function hash_hmac(
        string $algo, string $secret_key,
        string $datastring,
        bool $binary = null, array $options = []
    )
    {
        $binary = $binary ?? false;

        $hmac = hash_hmac($algo, $datastring, $secret_key, $binary);

        return $hmac;
    }

    public function hash_hmac_equals(
        string $user_hmac,
        string $algo, string $secret_key,
        string $user_datastring,
        bool $binary = null, array $options = []
    )
    {
        $known_hmac = $this->hash_hmac(
            $algo, $secret_key,
            $user_datastring,
            $binary, $options
        );

        return hash_equals($known_hmac, $user_hmac);
    }


    public function dec2numbase(string $decString, $alphabetTo, bool $oneBasedTo = null) : string
    {
        $theBcmath = Lib::bcmath();

        if (! $this->type_base_dec($_decString, $decString)) {
            throw new LogicException(
                [ 'The `decString` should be valid `baseDec`', $decString ]
            );
        }

        if (! $this->type_alphabet($_alphabetTo, $alphabetTo)) {
            throw new LogicException(
                [ 'The `alphabetTo` should be valid alphabet', $alphabetTo ]
            );
        }

        $alphabetToValue = $_alphabetTo->getValue();
        $alphabetToLen = $_alphabetTo->getLength();

        if ($alphabetToValue === '0123456789') {
            return $_decString;
        }

        $_oneBasedTo = null
            ?? $oneBasedTo
            ?? ($alphabetToValue[ 0 ] !== '0');

        $baseTo = $alphabetToLen;
        $baseToString = (string) $baseTo;

        $result = '';

        $left = $_decString;
        if ($_oneBasedTo) {
            if (bccomp($left, '0', 1) === 0) {
                throw new RuntimeException(
                    [ 'The `decInteger` should be greater than zero due to `oneBasedTo` is set to TRUE', $alphabetTo, $oneBasedTo ]
                );
            }
        }

        do {
            if ($_oneBasedTo) {
                $left = bcsub($left, '1', 0);
            }

            $mod = bcmod($left, $baseToString, 0);

            $result = mb_substr($_alphabetTo, (int) $mod, 1) . $result;

            $left = bcdiv($left, $baseToString, 0);
        } while ( bccomp($left, '0', 1) > 0 );

        return $result;
    }

    public function numbase2dec(string $numbaseString, $alphabetFrom, bool $oneBasedFrom = null) : string
    {
        $theBcmath = Lib::bcmath();
        $theMb = Lib::mb();

        if (! $this->type_base($_numbaseString, $numbaseString, $alphabetFrom)) {
            throw new LogicException(
                [
                    'The `numbaseString` should be valid `base` for given alphabet',
                    $numbaseString,
                    $alphabetFrom,
                ]
            );
        }

        if ($alphabetFrom === '0123456789') {
            return $_numbaseString;
        }

        $_oneBasedFrom = null
            ?? $oneBasedFrom
            ?? ($alphabetFrom[ 0 ] !== '0');

        $numbaseStringLen = mb_strlen($_numbaseString);

        $alphabetFromLen = mb_strlen($alphabetFrom);

        $baseFrom = $alphabetFromLen;
        $baseFromString = (string) $baseFrom;

        $result = '0';

        for ( $i = 1; $i <= $numbaseStringLen; $i++ ) {
            $chr = mb_substr($_numbaseString, $i - 1, 1);

            $mod = mb_strpos($alphabetFrom, $chr);
            if (false === $mod) {
                throw new LogicException(
                    [ 'The `baseInteger` contains char that is outside `alphabetFrom`: ' . $chr, $chr ]
                );
            }

            $mod = $_oneBasedFrom
                ? bcadd($mod, '1', 0)
                : $mod;

            $pow = bcpow($baseFromString, ($numbaseStringLen - $i), 0);

            $digit = bcmul($mod, $pow, 0);

            $result = bcadd($result, $digit, 0);
        }

        return $result;
    }


    /**
     * > функция переводит число из двоичной системы в другую, являющуюся степенью двойки, не переводя в десятичную систему
     *
     * > основное отличие `bin2numbase` от `bin2base` в считывании битов слева-направо и справа-налево соответственно
     * > при переводе числа в систему счисления pow(2,n) (например, из bin(2^1) в hex(2^3)), данные читаются справа-налево (т.к. число имеет конец)
     */
    public function bin2numbase(string $binary, $alphabetTo) : string
    {
        if (! $this->type_base_bin($_binary, $binary)) {
            throw new LogicException(
                [ 'The `binary` should be valid `baseBin`', $binary ]
            );
        }

        if (! $this->type_alphabet($_alphabetTo, $alphabetTo)) {
            throw new LogicException(
                [ 'The `alphabetTo` should be valid alphabet', $alphabetTo ]
            );
        }

        $alphabetToValue = $_alphabetTo->getValue();
        $alphabetToLen = $_alphabetTo->getLength();

        if ($alphabetToValue === '01') {
            return $_binary;
        }

        $binaryLen = strlen($_binary);

        $baseTo = $alphabetToLen;
        if (! $this->isPowerOf2($baseTo)) {
            throw new LogicException(
                [
                    'The `alphabetTo` length should be a power of 2: ' . $alphabetTo,
                    $alphabetTo,
                ]
            );
        }

        $bytesCnt = (int) log($baseTo, 2);

        $result = '';

        $buff = '';
        $left = $bytesCnt;
        for ( $i = $binaryLen; $i >= 1; $i-- ) {
            $buff = $_binary[ $i - 1 ] . $buff;

            $left--;

            if ($left === 0) {
                $idx = bindec($buff);

                $result .= $alphabetToValue[ $idx ];

                $left = $bytesCnt;
                $buff = '';
            }
        }

        if ('' !== $buff) {
            $idx = bindec($buff);

            $result .= $alphabetToValue[ $idx ];
        }

        return $result;
    }

    public function numbase2bin(string $numbaseString, $alphabetFrom) : string
    {
        $theMb = Lib::mb();

        if (! $this->type_base($_numbaseString, $numbaseString, $alphabetFrom)) {
            throw new LogicException(
                [
                    'The `numbaseString` should be valid `base` of given `alphabetFrom`',
                    $numbaseString,
                    $alphabetFrom,
                ]
            );
        }

        $numbaseStringLen = mb_strlen($_numbaseString);

        $alphabetFromLen = mb_strlen($alphabetFrom);

        $baseFrom = $alphabetFromLen;
        if (! $this->isPowerOf2($baseFrom)) {
            throw new LogicException(
                [
                    'The `alphabetFrom` length should be a power of 2: ' . $alphabetFrom,
                    $alphabetFrom,
                ]
            );
        }

        $bytesCnt = (int) log($baseFrom, 2);

        $result = '';

        $buff = '';
        $left = 8;
        for ( $i = 0; $i < $numbaseStringLen; $i++ ) {
            $idx = mb_strpos($alphabetFrom, $_numbaseString[ $i ]);

            $bin = decbin($idx);

            $bin = str_pad($bin, $bytesCnt, '0', STR_PAD_LEFT);

            for ( $ii = $bytesCnt; $ii >= 1; $ii-- ) {
                $buff = $bin[ $ii - 1 ] . $buff;

                $left--;

                if ($left === 0) {
                    $result = $buff . $result;

                    $left = 8;
                    $buff = '';
                }
            }
        }

        return $result;
    }


    public function numbase2numbase(
        string $numbaseString,
        $alphabetTo, $alphabetFrom,
        bool $oneBasedTo = null, bool $oneBasedFrom = null
    ) : string
    {
        if (! $this->type_base($_numbaseString, $numbaseString, $alphabetFrom)) {
            throw new LogicException(
                [
                    'The `numbaseString` should be valid `base` of given `alphabetFrom`',
                    $numbaseString,
                    $alphabetFrom,
                ]
            );
        }

        if (! $this->type_alphabet($_alphabetTo, $alphabetTo)) {
            throw new LogicException(
                [ 'The `alphabetTo` should be valid alphabet', $alphabetTo ]
            );
        }

        $result = null;

        if ($oneBasedFrom === $oneBasedTo) {
            if ($alphabetFrom === $_alphabetTo) {
                $result = $_numbaseString;

            } elseif ($alphabetFrom === '01234567890') {
                $result = $this->dec2numbase($_numbaseString, $_alphabetTo, $oneBasedTo);

            } elseif ($_alphabetTo === '01234567890') {
                $result = $this->numbase2dec($_numbaseString, $alphabetFrom, $oneBasedFrom);
            }
        }

        if (null === $result) {
            $result = $_numbaseString;
            $result = $this->numbase2dec($result, $alphabetFrom, $oneBasedFrom);
            $result = $this->dec2numbase($result, $_alphabetTo, $oneBasedTo);
        }

        return $result;
    }


    public function base36_encode(string $string) : string
    {
        $result = $this->baseX_encode($string, static::ALPHABET_BASE_36);

        return $result;
    }

    public function base36_decode(string $numbaseString) : string
    {
        $result = $this->baseX_decode($numbaseString, static::ALPHABET_BASE_36);

        return $result;
    }


    public function base58_encode(string $string) : string
    {
        $result = $this->baseX_encode($string, static::ALPHABET_BASE_58);

        return $result;
    }

    public function base58_decode(string $numbaseString) : string
    {
        $result = $this->baseX_decode($numbaseString, static::ALPHABET_BASE_58);

        return $result;
    }


    public function base62_encode(string $string) : string
    {
        $result = $this->baseX_encode($string, static::ALPHABET_BASE_62);

        return $result;
    }

    public function base62_decode(string $numbaseString) : string
    {
        $result = $this->baseX_decode($numbaseString, static::ALPHABET_BASE_62);

        return $result;
    }


    public function base64_encode(string $string) : string
    {
        $gen = $this->base64_encode_it($string);

        $result = '';
        foreach ( $gen as $baseLetter ) {
            $result .= $baseLetter;
        }

        return $result;
    }

    public function base64_decode(string $baseString) : string
    {
        $gen = $this->base64_decode_it($baseString);

        $result = '';
        foreach ( $gen as $chr ) {
            $result .= $chr;
        }

        return $result;
    }

    /**
     * @return \Generator<string>
     */
    public function base64_encode_it($strings) : \Generator
    {
        $gen = $this->baseX_encode_it(
            $strings,
            static::ALPHABET_BASE_64_RFC4648
        );

        return $gen;
    }

    /**
     * @return \Generator<string>
     */
    public function base64_decode_it($base64Strings, bool $throw = null) : \Generator
    {
        $gen = $this->baseX_decode_it(
            $base64Strings,
            static::ALPHABET_BASE_64_RFC4648,
            $throw
        );

        return $gen;
    }


    public function base64_encode_urlsafe(string $string) : string
    {
        $gen = $this->base64_encode_urlsafe_it($string);

        $result = '';
        foreach ( $gen as $baseLetter ) {
            $result .= $baseLetter;
        }

        return $result;
    }

    public function base64_decode_urlsafe(string $baseString) : string
    {
        $gen = $this->base64_decode_urlsafe_it($baseString);

        $result = '';
        foreach ( $gen as $chr ) {
            $result .= $chr;
        }

        return $result;
    }


    /**
     * @return \Generator<string>
     */
    public function base64_encode_urlsafe_it($strings) : \Generator
    {
        $gen = $this->baseX_encode_it(
            $strings,
            static::ALPHABET_BASE_64_RFC4648_URLSAFE
        );

        return $gen;
    }

    /**
     * @return \Generator<string>
     */
    public function base64_decode_urlsafe_it($base64Strings, bool $throw = null) : \Generator
    {
        $gen = $this->baseX_decode_it(
            $base64Strings,
            static::ALPHABET_BASE_64_RFC4648_URLSAFE,
            $throw
        );

        return $gen;
    }


    public function baseX_encode(string $string, $alphabetTo) : string
    {
        if ('' === $string) {
            return '';
        }

        $theBcmath = Lib::bcmath();

        if (! $this->type_alphabet($_alphabetTo, $alphabetTo)) {
            throw new LogicException(
                [ 'The `alphabetTo` should be valid alphabet', $alphabetTo ]
            );
        }

        $alphabetToValue = $_alphabetTo->getValue();
        $alphabetToLen = $_alphabetTo->getLength();

        $baseTo = $alphabetToLen;
        $baseToString = (string) $baseTo;

        $stringSize = strlen($string);

        $digits = [ 0 ];
        for ( $i = 0; $i < $stringSize; $i++ ) {
            $digitsCnt = count($digits);

            $ord = ord($string[ $i ]);
            $ordString = (string) $ord;

            for ( $ii = 0; $ii < $digitsCnt; $ii++ ) {
                $digits[ $ii ] = bcmul($digits[ $ii ], '256', 0);
            }

            $digits[ 0 ] = bcadd($digits[ 0 ], $ordString);

            $overflow = '0';
            for ( $ii = 0; $ii < $digitsCnt; $ii++ ) {
                $digits[ $ii ] = bcadd($digits[ $ii ], $overflow, 0);

                $overflow = bcdiv($digits[ $ii ], $baseToString, 0);

                $digits[ $ii ] = bcmod($digits[ $ii ], $baseToString, 0);
            }

            while ( bccomp($overflow, '0', 1) > 0 ) {
                $digits[] = bcmod($overflow, $baseToString, 0);

                $overflow = bcdiv($overflow, $baseToString, 0);
            }
        }

        for ( $i = 0; $i < ($stringSize - 1); $i++ ) {
            if (ord($string[ $i ]) !== 0) {
                break;
            }

            $digits[] = '0';
        }

        $result = '';
        foreach ( array_reverse($digits) as $digit ) {
            $result .= $alphabetToValue[ $digit ];
        }

        return $result;
    }

    public function baseX_decode(string $numbaseString, $alphabetFrom) : string
    {
        if ('' === $numbaseString) {
            return '';
        }

        $theBcmath = Lib::bcmath();
        $theMb = Lib::mb();

        if (! $this->type_base($_numbaseString, $numbaseString, $alphabetFrom)) {
            throw new LogicException(
                [
                    'The `numbaseString` should be valid `base` of given `alphabetFrom`',
                    $numbaseString,
                    $alphabetFrom,
                ]
            );
        }

        $alphabetFromLen = mb_strlen($alphabetFrom);
        $alphabetFromZero = mb_substr($alphabetFrom, 0, 1);

        $baseFrom = $alphabetFromLen;
        $baseFromString = (string) $baseFrom;

        $numbaseStringLen = mb_strlen($_numbaseString);

        $bytes = [ '0' ];
        for ( $i = 0; $i < $numbaseStringLen; $i++ ) {
            $bytesCnt = count($bytes);

            $chr = mb_substr($_numbaseString, $i, 1);

            for ( $ii = 0; $ii < $bytesCnt; $ii++ ) {
                $bytes[ $ii ] = bcmul($bytes[ $ii ], $baseFromString, 0);
            }

            $idx = mb_strpos($alphabetFrom, $chr);

            $bytes[ 0 ] = bcadd($bytes[ 0 ], $idx, 0);

            $overflow = '0';
            for ( $ii = 0; $ii < $bytesCnt; $ii++ ) {
                $bytes[ $ii ] = bcadd($bytes[ $ii ], $overflow, 0);

                $overflow = bcdiv($bytes[ $ii ], '256', 0);

                $bytes[ $ii ] = bcmod($bytes[ $ii ], '256', 0);
            }

            while ( bccomp($overflow, '0', 1) > 0 ) {
                $bytes[] = bcmod($overflow, '256', 0);

                $overflow = bcdiv($overflow, '256', 0);
            }
        }

        for ( $i = 0; $i < $numbaseStringLen; $i++ ) {
            $chr = mb_substr($numbaseString, $i, 1);

            if ($chr !== $alphabetFromZero) {
                break;
            }

            $bytes[] = 0;
        }

        $result = array_reverse($bytes);

        $result = array_map('chr', $result);

        $result = implode('', $result);

        return $result;
    }

    /**
     * @return \Generator<string>
     */
    public function baseX_encode_it($strings, $alphabetTo) : \Generator
    {
        $theBcmath = Lib::bcmath();
        $theMb = Lib::mb();

        if (! $this->type_alphabet($_alphabetTo, $alphabetTo)) {
            throw new LogicException(
                [ 'The `alphabetFrom` should be valid alphabet', $alphabetTo ]
            );
        }

        $baseTo = $_alphabetTo->getLength();
        if (! $this->isPowerOf2($baseTo)) {
            throw new LogicException(
                [
                    'The `alphabetTo` length should be a power of 2: ' . $alphabetTo,
                    $alphabetTo,
                ]
            );
        }

        $bytesCnt = (int) log($baseTo, 2);

        $gen = $this->text2bin_it($strings);

        $gen = $this->bin2base_it($gen, $_alphabetTo);

        $binaryLen = '0';
        foreach ( $gen as [ $binaryLen, $baseLetter ] ) {
            yield $baseLetter;
        }

        $bytesPerBlock = $theBcmath->bclcm('8', $bytesCnt);
        $bytesPerBlock = bcdiv($bytesPerBlock, '8');

        $padCnt = $binaryLen;
        $padCnt = bcmod($padCnt, $bytesPerBlock, 0);
        $padCnt = bcsub($bytesPerBlock, $padCnt, 0);
        $padCnt = bcmod($padCnt, $bytesPerBlock, 0);

        while ( bccomp($padCnt, '0', 1) > 0 ) {
            yield '=';

            $padCnt = bcsub($padCnt, '1', 0);
        }
    }

    /**
     * @return \Generator<string>
     */
    public function baseX_decode_it($base64Strings, $alphabetFrom, bool $throw = null) : \Generator
    {
        $theParse = Lib::parse();
        $theStr = Lib::str();

        $gen = $theStr->rtrim_it($base64Strings, '=');

        $gen = $this->base2bin_it($gen, $alphabetFrom, $throw);

        foreach ( $gen as $bin ) {
            $ord = bindec($bin);

            $chr = chr($ord);

            yield $chr;
        }
    }


    /**
     * > функция кодирует двоичный поток бит по принципу base64_encode
     * > это очень похоже на перевод в другую систему счисления, только чтение бит происходит слева-направо
     *
     * > основное отличие `bin2numbase` от `bin2base` в считывании битов слева-направо и справа-налево соответственно
     * > при кодировании потока бит в base{pow(2,n)} сам поток может быть бесконечным, что применяется в сетевом уровне и работе со stream (т.к. поток не имеет окончания)
     */
    public function bin2base($binaries, $alphabetTo) : string
    {
        $result = '';

        $gen = $this->bin2base_it($binaries, $alphabetTo);

        foreach ( $gen as [ $binaryLen, $baseLetter ] ) {
            $result .= $baseLetter;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function base2bin($baseStrings, $alphabetFrom) : array
    {
        $result = [];

        $gen = $this->base2bin_it($baseStrings, $alphabetFrom);

        foreach ( $gen as $bin ) {
            $result[] = $bin;
        }

        return $result;
    }

    /**
     * @return \Generator<string>
     */
    public function bin2base_it($binaries, $alphabetTo) : \Generator
    {
        $theBcmath = Lib::bcmath();

        $_binaries = is_iterable($binaries)
            ? $binaries
            : (is_string($binaries) ? [ $binaries ] : []);

        if (! $this->type_alphabet($_alphabetTo, $alphabetTo)) {
            throw new LogicException(
                [ 'The `alphabetTo` should be valid alphabet', $alphabetTo ]
            );
        }

        $alphabetToValue = $_alphabetTo->getValue();
        $alphabetToLen = $_alphabetTo->getLength();

        $baseTo = $alphabetToLen;
        if (! $this->isPowerOf2($baseTo)) {
            throw new LogicException(
                [
                    'The `alphabetTo` length should be a power of 2: ' . $alphabetTo,
                    $alphabetTo,
                ]
            );
        }

        $bitsCnt = (int) log($baseTo, 2);

        $binarySizeTotal = '0';
        $binaryBuff = '';

        $bitsLeft = $bitsCnt;
        foreach ( $_binaries as $binary ) {
            if (! $this->type_base_bin($_binary, $binary)) {
                throw new LogicException(
                    [ 'Each of `binaries` must be valid `baseBin`', $binary ]
                );
            }

            $binarySize = strlen($_binary);

            for ( $ii = 0; $ii < $binarySize; $ii++ ) {
                $binaryBuff .= $_binary[ $ii ];
                $binarySizeTotal = bcadd($binarySizeTotal, '1', 0);

                $bitsLeft--;

                if ($bitsLeft === 0) {
                    $baseLetterIdx = bindec($binaryBuff);
                    $baseLetter = $alphabetToValue[ $baseLetterIdx ];

                    yield [ $binarySizeTotal, $baseLetter ];

                    $binaryBuff = '';

                    $bitsLeft = $bitsCnt;
                }
            }
        }

        if ('' !== $binaryBuff) {
            $binaryBuff = str_pad($binaryBuff, $bitsCnt, '0', STR_PAD_RIGHT);
            $binarySizeTotal = bcadd($binarySizeTotal, '1', 0);

            $baseLetterIdx = bindec($binaryBuff);
            $baseLetter = $alphabetToValue[ $baseLetterIdx ];

            yield [ $binarySizeTotal, $baseLetter ];
        }
    }

    /**
     * @return \Generator<string>
     */
    public function base2bin_it($baseStrings, $alphabetFrom, bool $throw = null) : \Generator
    {
        $throw = $throw ?? true;

        $theParse = Lib::parse();
        $theMb = Lib::mb();

        $_baseStrings = is_iterable($baseStrings)
            ? $baseStrings
            : (is_string($baseStrings) ? [ $baseStrings ] : []);

        $_alphabetFrom = $theParse->alphabet($alphabetFrom);
        if (null === $_alphabetFrom) {
            if ($throw) {
                throw new LogicException(
                    [ 'The `alphabetTo` should be valid alphabet', $alphabetFrom ]
                );
            }
        }

        $alphabetFromLen = $_alphabetFrom->getLength();
        $alphabetFromRegexNot = $_alphabetFrom->getRegexNot();

        $baseFrom = $_alphabetFrom->getLength();

        if (! $this->isPowerOf2($baseFrom)) {
            throw new LogicException(
                [
                    'The `alphabetFrom` length should be a power of 2: ' . $alphabetFrom,
                    $alphabetFrom,
                ]
            );
        }

        $bytesCnt = (int) log($baseFrom, 2);

        $binaryBuff = '';
        $left = 8;
        foreach ( $_baseStrings as $baseString ) {
            if ('' === $baseString) {
                continue;
            }

            if (preg_match($alphabetFromRegexNot, $baseString)) {
                if ($throw) {
                    throw new LogicException(
                        [ 'Each of `baseStrings` should be valid baseString of given alphabet', $baseString, $alphabetFrom ]
                    );
                }

                continue;
            }

            $_baseString = $baseString;

            $baseStringLen = mb_strlen($_baseString);

            for ( $i = 0; $i < $baseStringLen; $i++ ) {
                $idx = mb_strpos($_alphabetFrom, $_baseString[ $i ]);

                $bin = decbin($idx);

                $bin = str_pad($bin, $bytesCnt, '0', STR_PAD_LEFT);

                for ( $ii = 0; $ii < $bytesCnt; $ii++ ) {
                    $binaryBuff .= $bin[ $ii ];

                    $left--;

                    if ($left === 0) {
                        yield $binaryBuff;

                        $left = 8;
                        $binaryBuff = '';
                    }
                }
            }
        }
    }


    /**
     * @return string[]
     */
    public function text2bin($strings) : array
    {
        $result = [];

        $gen = $this->text2bin_it($strings);

        foreach ( $gen as $binary ) {
            $result[] = $binary;
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function bin2text($binaries) : array
    {
        $result = [];

        $gen = $this->bin2text_it($binaries);

        foreach ( $gen as $binary ) {
            $result[] = $binary;
        }

        return $result;
    }

    /**
     * @return \Generator<string>
     */
    public function text2bin_it($strings) : \Generator
    {
        $theMb = Lib::mb();

        $_strings = is_iterable($strings)
            ? $strings
            : (is_string($strings) ? [ $strings ] : []);

        foreach ( $_strings as $string ) {
            if ('' === $string) {
                continue;
            }

            $len = mb_strlen($string);

            for ( $i = 0; $i < $len; $i++ ) {
                $letter = mb_substr($string, $i, 1);

                $bytes = unpack('C*', $letter);

                $binary = '';
                foreach ( $bytes as $byte ) {
                    $bin = decbin($byte);
                    $bin = str_pad($bin, 8, '0', STR_PAD_LEFT);

                    $binary .= $bin;
                }

                yield $binary;
            }
        }
    }

    /**
     * @return \Generator<string>
     */
    public function bin2text_it($binaries, bool $throw = null) : \Generator
    {
        $throw = $throw ?? true;

        $theMb = Lib::mb();

        $_binaries = is_iterable($binaries)
            ? $binaries
            : (is_string($binaries) ? [ $binaries ] : []);

        $error = null;

        $buff = '';
        $buffLen = 0;

        $bytes = [];
        $followingBitsCount = null;

        foreach ( $binaries as $binary ) {
            $bits = str_split($binary);

            foreach ( $bits as $bit ) {
                $buff .= $bit;
                $buffLen += 1;

                if ($buffLen === 8) {
                    $bin = substr($buff, 0, 8);
                    $byte = bindec($bin);

                    if ($followingBitsCount === null) {
                        if (($byte & 0b11111000) === 0b11110000) {
                            $bytes[] = $byte;

                            $buff = substr($buff, 8);
                            $buffLen -= 8;

                            $followingBitsCount = 24;

                        } elseif (($byte & 0b11110000) === 0b11100000) {
                            $bytes[] = $byte;

                            $buff = substr($buff, 8);
                            $buffLen -= 8;

                            $followingBitsCount = 16;

                        } elseif (($byte & 0b11100000) === 0b11000000) {
                            $bytes[] = $byte;

                            $buff = substr($buff, 8);
                            $buffLen -= 8;

                            $followingBitsCount = 8;

                        } elseif (($byte & 0b10000000) === 0) {
                            $bytes[] = $byte;

                            $buff = substr($buff, 8);
                            $buffLen -= 8;

                            $followingBitsCount = 0;

                        } else {
                            $error = [
                                'The first `byte` should be one of: '
                                . implode('|',
                                    [
                                        '0xxxxxxx',
                                        '110xxxxxx',
                                        '1110xxxxx',
                                        '11110xxxx',
                                    ]
                                ),
                                $bin,
                            ];
                        }

                    } elseif ($followingBitsCount > 0) {
                        if (($byte & 0b11000000) !== 0b10000000) {
                            $error = [ 'The `nextByte` should be 10xxxxxx', $bin ];

                        } else {
                            $bytes[] = $byte;

                            $buff = substr($buff, 8);
                            $buffLen -= 8;

                            $followingBitsCount -= 8;
                        }
                    }
                }

                if ($followingBitsCount === 0) {
                    if ($buffLen > 0) {
                        $error = [ 'The `buff` should be empty after parsing bytes', $buff ];

                    } else {
                        $letter = '';
                        foreach ( $bytes as $byte ) {
                            $letter .= chr($byte);
                        }

                        yield $letter;

                        $buff = '';
                        $buffLen = 0;

                        $bytes = [];
                        $followingBitsCount = null;
                    }
                }

                if ($error) {
                    if ($throw) {
                        throw new RuntimeException($error);
                    }

                    $buff = '';
                    $buffLen = 0;

                    $bytes = [];
                    $followingBitsCount = null;
                }
            }
        }
    }


    protected function isPowerOf2(int $n) : bool
    {
        return ($n > 0) && (($n & ($n - 1)) === 0);
    }
}
