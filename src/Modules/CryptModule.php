<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
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

    // >>>>>>>>>>>>>>>>>>>>> '0123456789'    . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'    . 'abcdefghijklmnopqrstuvwxyz'
    // >>>>>>>>>>>>>>>>>>>>> '123456789'[~0] . 'ABCDEFGHJKLMNPQRSTUVWXYZ'[~IO] . 'abcdefghijkmnopqrstuvwxyz'[~l]
    const ALPHABET_BASE_58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    const ALPHABET_BASE_32_RFC4648         = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    const ALPHABET_BASE_32_RFC4648_HEXLIKE = '0123456789ABCDEFGHIJKLMNOPQRSTUV'; // > ALPHABET_BASE_32
    const ALPHABET_BASE_64_RFC4648         = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    const ALPHABET_BASE_64_RFC4648_URLSAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

    /**
     * @noinspection PhpDuplicateArrayKeysInspection
     */
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
        self::ALPHABET_BASE_32_RFC4648_HEXLIKE => true,
        self::ALPHABET_BASE_64_RFC4648         => true,
        self::ALPHABET_BASE_64_RFC4648_URLSAFE => true,
    ];


    /**
     * @return Ret<string>
     */
    public function type_base($value, $alphabet)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        $alphabetValid = $theType->alphabet($alphabet)->orThrow();

        if (preg_match($alphabetValid->getRegexNot(), $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid base of given alphabet', $value, $alphabet ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_base_bin($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (preg_match('~[^01]~', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid binary string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_base_oct($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (preg_match('~[^01234567]~', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid octal string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_base_dec($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (preg_match('~[^0123456789]~', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid decimal string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }

    /**
     * @return Ret<string>
     */
    public function type_base_hex($value)
    {
        $theType = Lib::$type;

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        if (preg_match('~[^0123456789ABCDEF]~', $valueStringNotEmpty)) {
            return Ret::err(
                [ 'The `value` should be valid hexademical string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($valueStringNotEmpty);
    }


    /**
     * @noinspection PhpMethodParametersCountMismatchInspection
     */
    public function hash(
        string $algo,
        string $datastring,
        ?bool $isModeBinary = null,
        array $options = []
    ) : string
    {
        $isModeBinary = $isModeBinary ?? false;

        $result = (PHP_VERSION_ID >= 80100)
            ? hash($algo, $datastring, $isModeBinary, $options)
            : hash($algo, $datastring, $isModeBinary);

        return $result;
    }

    public function hash_equals(
        string $user_hash,
        string $algo,
        string $user_datastring,
        ?bool $isModeBinary = null,
        array $options = []
    )
    {
        $known_hash = $this->hash(
            $algo,
            $user_datastring,
            $isModeBinary,
            $options
        );

        return hash_equals($known_hash, $user_hash);
    }

    public function hash_hmac(
        string $algo, string $secret_key,
        string $datastring,
        ?bool $isModeBinary = null,
        array $options = []
    )
    {
        $isModeBinary = $isModeBinary ?? false;

        $hmac = hash_hmac($algo, $datastring, $secret_key, $isModeBinary);

        return $hmac;
    }

    public function hash_hmac_equals(
        string $user_hmac,
        string $algo, string $secret_key,
        string $user_datastring,
        ?bool $isModeBinary = null,
        array $options = []
    )
    {
        $known_hmac = $this->hash_hmac(
            $algo, $secret_key,
            $user_datastring,
            $isModeBinary,
            $options
        );

        return hash_equals($known_hmac, $user_hmac);
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
        $thePhp = Lib::$php;

        $stringsIt = $thePhp->to_iterable($strings);

        foreach ( $stringsIt as $string ) {
            if ('' === $string) {
                continue;
            }

            $len = strlen($string);

            for ( $i = 0; $i < $len; $i++ ) {
                $bin = $string[ $i ];

                $bin = ord($bin);
                $bin = decbin($bin);
                $bin = str_pad($bin, 8, '0', STR_PAD_LEFT);

                yield $bin;
            }
        }
    }

    /**
     * @return \Generator<string>
     */
    public function bin2text_it($binaries, ?bool $isThrow = null) : \Generator
    {
        $isThrow = $isThrow ?? true;

        $thePhp = Lib::$php;

        $binariesIt = $thePhp->to_iterable($binaries);

        $error = null;

        $buff = '';
        $buffLen = 0;

        $bytes = [];
        $followingBitsCount = null;

        foreach ( $binariesIt as $binary ) {
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
                                ''
                                . 'The first `byte` should be one of: '
                                . implode('|',
                                    [
                                        '0xxxxxxx',
                                        '110xxxxxx',
                                        '1110xxxxx',
                                        '11110xxxx',
                                    ]
                                ),
                                //
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
                    if ($isThrow) {
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


    /**
     * @noinspection PhpLoopCanBeReplacedWithImplodeInspection
     */
    public function base64_encode(string $string) : string
    {
        $gen = $this->base64_encode_it($string);

        $result = '';
        foreach ( $gen as $baseLetter ) {
            $result .= $baseLetter;
        }

        return $result;
    }

    /**
     * @noinspection PhpLoopCanBeReplacedWithImplodeInspection
     */
    public function base64_decode(string $base64String) : string
    {
        $gen = $this->base64_decode_it($base64String);

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
    public function base64_decode_it($base64Strings, ?bool $isThrow = null) : \Generator
    {
        $gen = $this->baseX_decode_it(
            $base64Strings,
            static::ALPHABET_BASE_64_RFC4648,
            $isThrow
        );

        return $gen;
    }


    /**
     * @noinspection PhpLoopCanBeReplacedWithImplodeInspection
     */
    public function base64_encode_urlsafe(string $string) : string
    {
        $gen = $this->base64_encode_urlsafe_it($string);

        $result = '';
        foreach ( $gen as $baseLetter ) {
            $result .= $baseLetter;
        }

        return $result;
    }

    /**
     * @noinspection PhpLoopCanBeReplacedWithImplodeInspection
     */
    public function base64_decode_urlsafe(string $base64UrlSafeString) : string
    {
        $gen = $this->base64_decode_urlsafe_it($base64UrlSafeString);

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
    public function base64_decode_urlsafe_it($base64UrlSafeStrings, ?bool $isThrow = null) : \Generator
    {
        $gen = $this->baseX_decode_it(
            $base64UrlSafeStrings,
            static::ALPHABET_BASE_64_RFC4648_URLSAFE,
            $isThrow
        );

        return $gen;
    }


    public function baseX_encode(string $string, $alphabetTo) : string
    {
        if ('' === $string) {
            return '';
        }

        $theType = Lib::$type;

        $alphabetToObject = $theType->alphabet($alphabetTo)->orThrow();

        $alphabetToValue = $alphabetToObject->getValue();
        $alphabetToLen = $alphabetToObject->getLength();

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

    public function baseX_decode(string $baseString, $alphabetFrom) : string
    {
        if ('' === $baseString) {
            return '';
        }

        $theType = Lib::$type;

        $baseStringValid = $theType->base($baseString, $alphabetFrom)->orThrow();

        $alphabetFromLen = mb_strlen($alphabetFrom);
        $alphabetFromZero = mb_substr($alphabetFrom, 0, 1);

        $baseFrom = $alphabetFromLen;
        $baseFromString = (string) $baseFrom;

        $numbaseStringLen = mb_strlen($baseStringValid);

        $bytes = [ '0' ];
        for ( $i = 0; $i < $numbaseStringLen; $i++ ) {
            $bytesCnt = count($bytes);

            $chr = mb_substr($baseStringValid, $i, 1);

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
            $chr = mb_substr($baseString, $i, 1);

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
        $theBcmath = Lib::$bcmath;
        $theType = Lib::$type;

        $theBcmath->assertExtension();

        $alphabetToValid = $theType->alphabet($alphabetTo)->orThrow();

        $baseTo = $alphabetToValid->getLength();

        if (! $this->isPowOf2($baseTo)) {
            throw new LogicException(
                [
                    'The `alphabetTo` length should be a power of 2: ' . $alphabetTo,
                    $alphabetTo,
                ]
            );
        }

        $bytesCnt = (int) log($baseTo, 2);

        $gen = $this->text2bin_it($strings);

        $gen = $this->bin2base_it($gen, $alphabetToValid);

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
    public function baseX_decode_it($baseStrings, $alphabetFrom, ?bool $isThrow = null) : \Generator
    {
        $theStr = Lib::$str;

        $gen = $theStr->rtrim_it($baseStrings, '=');

        $gen = $this->base2bin_it($gen, $alphabetFrom, $isThrow);

        foreach ( $gen as $bin ) {
            $ord = bindec($bin);

            $chr = chr($ord);

            yield $chr;
        }
    }


    /**
     * > функция кодирует двоичный поток бит по принципу base64_encode
     * > это очень похоже на перевод в другую систему счисления, только чтение бит происходит LTR (при кодировании числа же - RTL)
     * > это быстрее и не требует окончания потока
     * > это менее безопасно, т.к. каждый байт можно подменить и итоговая фраза раскодируется обратно без ошибок
     *
     * @noinspection PhpLoopCanBeReplacedWithImplodeInspection
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
        $thePhp = Lib::$php;
        $theType = Lib::$type;

        $binariesIt = $thePhp->to_iterable($binaries);

        $alphabetToValid = $theType->alphabet($alphabetTo)->orThrow();

        $alphabetToValue = $alphabetToValid->getValue();
        $alphabetToLen = $alphabetToValid->getLength();

        $baseTo = $alphabetToLen;
        if (! $this->isPowOf2($baseTo)) {
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
        foreach ( $binariesIt as $binary ) {
            $binaryValid = $this->type_base_bin($binary)->orThrow();

            $binarySize = strlen($binaryValid);

            for ( $ii = 0; $ii < $binarySize; $ii++ ) {
                $binaryBuff .= $binaryValid[ $ii ];
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
    public function base2bin_it($baseStrings, $alphabetFrom, ?bool $isThrow = null) : \Generator
    {
        $isThrow = $isThrow ?? true;

        $theMb = Lib::$mb;
        $thePhp = Lib::$php;
        $theType = Lib::$type;

        $theMb->assertExtension();

        $baseStringsIt = $thePhp->to_iterable($baseStrings);

        $alphabetFromValid = $theType->alphabet($alphabetFrom)->orThrow();

        $alphabetFromLen = $alphabetFromValid->getLength();
        $alphabetFromRegexNot = $alphabetFromValid->getRegexNot();

        $baseFrom = $alphabetFromLen;

        if (! $this->isPowOf2($baseFrom)) {
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
        foreach ( $baseStringsIt as $baseString ) {
            if ('' === $baseString) {
                continue;
            }

            if (preg_match($alphabetFromRegexNot, $baseString)) {
                if ($isThrow) {
                    throw new LogicException(
                        [ 'Each of `baseStrings` should be a valid `baseString` of given alphabet', $baseString, $alphabetFrom ]
                    );
                }

                continue;
            }

            $_baseString = $baseString;

            $baseStringLen = mb_strlen($_baseString);

            for ( $i = 0; $i < $baseStringLen; $i++ ) {
                $idx = mb_strpos($alphabetFromValid, $_baseString[ $i ]);

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
     * > функция переводит число из двоичной системы в другую, являющуюся степенью двойки, не переводя в десятичную систему
     * > например, 2 -> 8 или 2 -> 64
     */
    public function bin2binbase(string $binary, $alphabetTo) : string
    {
        $theType = Lib::$type;

        $binaryValid = $this->type_base_bin($binary)->orThrow();
        $alphabetToValid = $theType->alphabet($alphabetTo)->orThrow();

        $alphabetToValue = $alphabetToValid->getValue();
        $alphabetToLen = $alphabetToValid->getLength();

        if ($alphabetToValue === '01') {
            return $binaryValid;
        }

        $binaryLen = strlen($binaryValid);

        $baseTo = $alphabetToLen;
        if (! $this->isPowOf2($baseTo)) {
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
            $buff = $binaryValid[ $i - 1 ] . $buff;

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

    /**
     * > функция переводит число из системы счисления, являющейся степенью двойки, в двоичную
     * > например, 8 -> 2 или 64 -> 2
     */
    public function binbase2bin(string $binbaseString, $alphabetFrom) : string
    {
        $theMb = Lib::$mb;

        $theMb->assertExtension();

        $binbaseStringValid = $this->type_base($binbaseString, $alphabetFrom)->orThrow();

        $binbaseStringLen = mb_strlen($binbaseStringValid);
        $alphabetFromLen = mb_strlen($alphabetFrom);

        $baseFrom = $alphabetFromLen;
        if (! $this->isPowOf2($baseFrom)) {
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
        for ( $i = 0; $i < $binbaseStringLen; $i++ ) {
            $idx = mb_strpos($alphabetFrom, $binbaseStringValid[ $i ]);

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


    /**
     * > функция переводит число из десятичной системы в любую другую
     * > например, 10 -> 58
     */
    public function dec2numbase(string $decString, $alphabetTo, ?bool $isOneBasedTo = null) : string
    {
        $theBcmath = Lib::$bcmath;
        $theType = Lib::$type;

        $theBcmath->assertExtension();

        $decStringValid = $this->type_base_dec($decString)->orThrow();
        $alphabetToValid = $theType->alphabet($alphabetTo)->orThrow();

        $alphabetToValue = $alphabetToValid->getValue();
        $alphabetToLen = $alphabetToValid->getLength();

        if ($alphabetToValue === '0123456789') {
            return $decStringValid;
        }

        $_oneBasedTo = null
            ?? $isOneBasedTo
            ?? ($alphabetToValue[ 0 ] !== '0');

        $baseTo = $alphabetToLen;
        $baseToString = (string) $baseTo;

        $result = '';

        $left = $decStringValid;
        if ($_oneBasedTo) {
            if (bccomp($left, '0', 1) === 0) {
                throw new RuntimeException(
                    [
                        'The `decInteger` should be GT 0 due to `oneBasedTo` is set to TRUE',
                        $alphabetTo,
                        $isOneBasedTo,
                    ]
                );
            }
        }

        do {
            if ($_oneBasedTo) {
                $left = bcsub($left, '1', 0);
            }

            $mod = bcmod($left, $baseToString, 0);

            $result = mb_substr($alphabetToValid, (int) $mod, 1) . $result;

            $left = bcdiv($left, $baseToString, 0);
        } while ( bccomp($left, '0', 1) > 0 );

        return $result;
    }

    /**
     * > функция переводит число из любой системы счисления в десятичную
     * > например, 58 -> 10
     */
    public function numbase2dec(string $numbaseString, $alphabetFrom, ?bool $isOneBasedFrom = null) : string
    {
        $theBcmath = Lib::$bcmath;
        $theMb = Lib::$mb;

        $theBcmath->assertExtension();
        $theMb->assertExtension();

        $numbaseStringValid = $this->type_base($numbaseString, $alphabetFrom)->orThrow();

        if ($alphabetFrom === '0123456789') {
            return $numbaseStringValid;
        }

        $_oneBasedFrom = null
            ?? $isOneBasedFrom
            ?? ($alphabetFrom[ 0 ] !== '0');

        $numbaseStringLen = mb_strlen($numbaseStringValid);

        $alphabetFromLen = mb_strlen($alphabetFrom);

        $baseFrom = $alphabetFromLen;
        $baseFromString = (string) $baseFrom;

        $result = '0';

        for ( $i = 1; $i <= $numbaseStringLen; $i++ ) {
            $chr = mb_substr($numbaseStringValid, $i - 1, 1);

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
     * > функция переводит число из любой системы счисления в любую другую
     */
    public function numbase2numbase(
        string $numbaseString,
        $alphabetTo, $alphabetFrom,
        ?bool $oneBasedTo = null, ?bool $oneBasedFrom = null
    ) : string
    {
        $theType = Lib::$type;

        $numbaseStringValid = $this->type_base($numbaseString, $alphabetFrom)->orThrow();
        $alphabetToValid = $theType->alphabet($alphabetTo)->orThrow();

        $result = null;

        if ($oneBasedFrom === $oneBasedTo) {
            if ($alphabetFrom === $alphabetToValid) {
                $result = $numbaseStringValid;

            } elseif ($alphabetFrom === '01234567890') {
                $result = $this->dec2numbase($numbaseStringValid, $alphabetToValid, $oneBasedTo);

            } elseif ($alphabetToValid === '01234567890') {
                $result = $this->numbase2dec($numbaseStringValid, $alphabetFrom, $oneBasedFrom);
            }
        }

        if (null === $result) {
            $result = $numbaseStringValid;
            $result = $this->numbase2dec($result, $alphabetFrom, $oneBasedFrom);
            $result = $this->dec2numbase($result, $alphabetToValid, $oneBasedTo);
        }

        return $result;
    }


    protected function isPowOf2(int $n) : bool
    {
        return ($n > 0) && (($n & ($n - 1)) === 0);
    }
}
