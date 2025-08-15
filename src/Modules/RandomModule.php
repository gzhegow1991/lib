<?php

/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class RandomModule
{
    /**
     * @var callable
     */
    protected static $fnUuid;

    /**
     * @param callable|false|null $fnUuid
     *
     * @return callable
     */
    public static function staticUuidFn($fnUuid = null)
    {
        $last = static::$fnUuid;

        if (null !== $fnUuid) {
            if (false === $fnUuid) {
                static::$fnUuid = null;

            } else {
                if (! is_callable($fnUuid)) {
                    throw new LogicException(
                        [ 'The `fnUuid` should be callable', $fnUuid ]
                    );
                }

                static::$fnUuid = $fnUuid;
            }
        }

        static::$fnUuid = static::$fnUuid ?? null;

        return $last;
    }


    public function __construct()
    {
        static::$fnUuid = static::$fnUuid ?? [ $this, 'fnUuid' ];
    }


    /**
     * @param string $refValue
     *
     * @return string
     */
    public function idIncrement(&$refValue) : string
    {
        $val = $refValue;

        if (is_int($val)) {
            if ($val === PHP_INT_MAX) {
                $theBcmath = Lib::bcmath();

                $val = bcadd($val, 1);

            } else {
                $val++;
            }

        } else {
            $theType = Lib::type();

            $theType->ctype_digit($val)->orThrow();

            if (false
                || (strlen($val) > strlen(PHP_INT_MAX))
                || (floatval($val) >= PHP_INT_MAX)
            ) {
                $theBcmath = Lib::bcmath();

                $val = bcadd($val, 1);

            } else {
                $val = ((int) $val) + 1;
            }
        }

        $valString = (string) $val;

        $refValue = $valString;

        return $valString;
    }


    public function uuid() : string
    {
        $fn = $this->staticUuidFn();

        $uuid = call_user_func($fn);

        return $uuid;
    }

    protected function fnUuid() : string
    {
        $bytes = $this->random_bytes(16);

        $arr = array_values(unpack('N1a/n1b/n1c/n1d/n1e/N1f', $bytes));
        $arr[ 2 ] = ($arr[ 2 ] & 0x0fff) | 0x4000;
        $arr[ 3 ] = ($arr[ 3 ] & 0x3fff) | 0x8000;

        array_unshift($arr, '%08x-%04x-%04x-%04x-%04x%08x');

        $uuid = call_user_func_array('sprintf', $arr);

        return $uuid;
    }


    /**
     * @return Ret<string>
     */
    public function type_uuid($value)
    {
        if (! is_string($value)) {
            return Ret::err(
                [ 'The `value` should be string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('' === $value) {
            return Ret::err(
                [ 'The `value` should be string, not-empty', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if (! preg_match($regex, $value)) {
            return Ret::err(
                [ 'The `value` should be valid uuid', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($value);
    }


    /**
     * @noinspection PhpComposerExtensionStubsInspection
     *
     * > $bytes = $random->bytes();
     * > var_dump(bin2hex($bytes)) // string(32) "00f6c04b144b41fad6a59111c126e1ee"
     */
    public function random_bytes(?int $len = null) : string
    {
        $len = $len ?? 16;

        if ($len < 1) $len = 1;

        $result = null;

        if (function_exists('random_bytes')) {
            try {
                $result = random_bytes($len);
            }
            catch ( \Exception $e ) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

        } elseif (function_exists('\\Sodium\\randombytes_buf')) {
            $result = \Sodium\randombytes_buf($len);

        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $result = openssl_random_pseudo_bytes($len);

        } elseif (file_exists('/dev/urandom')) {
            $handle = fopen('/dev/urandom', 'rb');

            if ($handle !== false) {
                stream_set_read_buffer($handle, 0);
                $ret = fread($handle, $len);
                fclose($handle);

                if (strlen($ret) != $len) {
                    throw new RuntimeException('Unexpected partial read from random device');
                }

                $result = $ret;
            }
        }

        if (null === $result) {
            throw new RuntimeException('No random device available');
        }

        return $result;
    }

    public function random_hex(?int $len = null) : string
    {
        $array = unpack('H*', $this->random_bytes($len));

        $result = array_shift($array);

        return $result;
    }

    public function random_int(int $min, int $max) : int
    {
        try {
            $rand = random_int($min, $max);
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $rand;
    }

    public function random_string(int $len, ?string $alphabet = null) : string
    {
        $alphabet = $alphabet ?? '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $theType = Lib::type();

        $lenIntPositive = $theType->int_positive($len)->orThrow();
        $alphabetValid = $theType->alphabet($alphabet)->orThrow();

        $alphabetLen = $alphabetValid->getLength();

        $min = 0;
        $max = $alphabetLen - 1;

        $rand = [];
        for ( $i = 0; $i < $lenIntPositive; ++$i ) {
            $randomInt = $this->random_int($min, $max);

            $rand[ $i ] = mb_substr($alphabetValid, $randomInt, 1);
        }

        $rand = implode('', $rand);

        return $rand;
    }


    public function random_base64_urlsafe(?int $len = null) : string
    {
        return Lib::crypt()->base64_encode_urlsafe($this->random_bytes($len));
    }

    public function random_base64(?int $len = null) : string
    {
        return Lib::crypt()->base64_encode($this->random_bytes($len));
    }

    public function random_base62(?int $len = null) : string
    {
        return Lib::crypt()->base62_encode($this->random_bytes($len));
    }

    public function random_base58(?int $len = null) : string
    {
        return Lib::crypt()->base58_encode($this->random_bytes($len));
    }

    public function random_base36(?int $len = null) : string
    {
        return Lib::crypt()->base36_encode($this->random_bytes($len));
    }
}
