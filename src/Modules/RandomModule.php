<?php
/**
 * @noinspection PhpComposerExtensionStubsInspection
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
    protected $stateFnUuidV4;
    /**
     * @var callable
     */
    protected $stateFnUuidV5;
    /**
     * @var callable
     */
    protected $stateFnUuidV7;

    /**
     * @param callable|false|null $fnUuidV4
     *
     * @return callable|null
     */
    public function stateFnUuidV4($fnUuidV4 = null)
    {
        $last = null;

        if ( $isChange = (null !== $fnUuidV4) ) {
            $last = $this->stateFnUuidV4;

            if ( false === $fnUuidV4 ) {
                $this->stateFnUuidV4 = null;

            } else {
                if ( ! is_callable($fnUuidV4) ) {
                    throw new LogicException(
                        [ 'The `fnUuidV4` should be callable', $fnUuidV4 ]
                    );
                }

                $this->stateFnUuidV4 = $fnUuidV4;
            }
        }

        if ( null === $this->stateFnUuidV4 ) {
            $this->stateFnUuidV4 = [ $this, 'fnUuidV4' ];
        }

        return $isChange ? $last : $this->stateFnUuidV4;
    }

    /**
     * @param callable|false|null $fnUuidV5
     *
     * @return callable|null
     */
    public function stateFnUuidV5($fnUuidV5 = null)
    {
        $last = null;

        if ( $isChange = (null !== $fnUuidV5) ) {
            $last = $this->stateFnUuidV5;

            if ( false === $fnUuidV5 ) {
                $this->stateFnUuidV5 = null;

            } else {
                if ( ! is_callable($fnUuidV5) ) {
                    throw new LogicException(
                        [ 'The `fnUuidV5` should be callable', $fnUuidV5 ]
                    );
                }

                $this->stateFnUuidV5 = $fnUuidV5;
            }
        }

        if ( null === $this->stateFnUuidV5 ) {
            $this->stateFnUuidV5 = [ $this, 'fnUuidV5' ];
        }

        return $isChange ? $last : $this->stateFnUuidV5;
    }

    /**
     * @param callable|false|null $fnUuidV7
     *
     * @return callable|null
     */
    public function stateFnUuidV7($fnUuidV7 = null)
    {
        $last = null;

        if ( $isChange = (null !== $fnUuidV7) ) {
            $last = $this->stateFnUuidV7;

            if ( false === $fnUuidV7 ) {
                $this->stateFnUuidV7 = null;

            } else {
                if ( ! is_callable($fnUuidV7) ) {
                    throw new LogicException(
                        [ 'The `fnUuidV7` should be callable', $fnUuidV7 ]
                    );
                }

                $this->stateFnUuidV7 = $fnUuidV7;
            }
        }

        if ( null === $this->stateFnUuidV7 ) {
            $this->stateFnUuidV7 = [ $this, 'fnUuidV7' ];
        }

        return $isChange ? $last : $this->stateFnUuidV7;
    }


    // public function __construct()
    // {
    // }

    public function __initialize()
    {
        return $this;
    }


    public function the_uuid_nil() : string
    {
        return '00000000-0000-0000-0000-000000000000';
    }


    /**
     * @return Ret<string>|string
     */
    public function type_uuid_nil($fb, $value)
    {
        if ( $this->the_uuid_nil() == $value ) {
            return Ret::ok($fb, $value);
        }

        return Ret::throw(
            $fb,
            [ 'The `value` should be nil-uuid', $value ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<string>|string
     */
    public function type_uuid($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $this->the_uuid_nil() === $valueString ) {
            return Ret::ok($fb, $valueString);
        }

        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-[1-8][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if ( ! preg_match($regex, $valueString) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid uuid', $valueString ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueString);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_uuid_not_nil($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-[1-8][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if ( ! preg_match($regex, $valueString) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid uuid', $valueString ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueString);
    }


    /**
     * @return Ret<string>|string
     */
    public function type_uuid_v4($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if ( ! preg_match($regex, $valueString) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid uuid', $valueString ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueString);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_uuid_v5($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-5[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if ( ! preg_match($regex, $valueString) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid uuid', $valueString ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueString);
    }

    /**
     * @return Ret<string>|string
     */
    public function type_uuid_v7($fb, $value)
    {
        $theType = Lib::type();

        $ret = $theType->string_not_empty($value);

        if ( ! $ret->isOk([ &$valueString ]) ) {
            return Ret::throw(
                $fb,
                $ret,
                [ __FILE__, __LINE__ ]
            );
        }

        $regex = '/^[0-9A-F]{8}-[0-9A-F]{4}-7[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if ( ! preg_match($regex, $valueString) ) {
            return Ret::throw(
                $fb,
                [ 'The `value` should be valid uuid', $valueString ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::ok($fb, $valueString);
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

        if ( $len < 1 ) $len = 1;

        $result = null;

        if ( function_exists('random_bytes') ) {
            try {
                $result = random_bytes($len);
            }
            catch ( \Throwable $e ) {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }

        } elseif ( function_exists('\\Sodium\\randombytes_buf') ) {
            $result = \Sodium\randombytes_buf($len);

        } elseif ( function_exists('openssl_random_pseudo_bytes') ) {
            $result = openssl_random_pseudo_bytes($len);

        } elseif ( file_exists('/dev/urandom') ) {
            $handle = fopen('/dev/urandom', 'rb');

            if ( $handle !== false ) {
                stream_set_read_buffer($handle, 0);
                $ret = fread($handle, $len);
                fclose($handle);

                if ( strlen($ret) != $len ) {
                    throw new RuntimeException('Unexpected partial read from random device');
                }

                $result = $ret;
            }
        }

        if ( null === $result ) {
            throw new RuntimeException('No random device available');
        }

        return $result;
    }

    public function random_hex(?int $len = null) : string
    {
        $bytes = $this->random_bytes($len);

        $array = unpack('H*', $bytes);

        $result = reset($array);

        return $result;
    }

    public function random_int(int $min, int $max) : int
    {
        try {
            $rand = random_int($min, $max);
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e);
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

            $rand[$i] = mb_substr($alphabetValid, $randomInt, 1);
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


    /**
     * > uuid v4 - полностью случайная последовательность символов
     */
    public function uuid_v4() : string
    {
        $fn = $this->stateFnUuidV4();

        $uuid = $fn();

        $this->type_uuid_v4([], $uuid);

        return $uuid;
    }

    /**
     * > uuid v5 - для одинаковых namespace/name всегда возвращает одинаковый UUID
     */
    public function uuid_v5(string $namespaceUuid, string $name) : string
    {
        $this->type_uuid([], $namespaceUuid);

        $fn = $this->stateFnUuidV5();

        $uuid = $fn($namespaceUuid, $name);

        $this->type_uuid_v5([], $uuid);

        return $uuid;
    }

    /**
     * > uuid v7 - случайная последовательность, однако каждая следующая при сортировке будет после предыдущей (время)
     */
    public function uuid_v7() : string
    {
        $fn = $this->stateFnUuidV7();

        $uuid = $fn();

        $this->type_uuid_v7([], $uuid);

        return $uuid;
    }

    protected function fnUuidV4() : string
    {
        $fn = 'random_int';

        $block1 = $fn(0, 0xFFFFFFFF);
        $block2 = $fn(0, 0xFFFF);

        $uuidV4Version = ($fn(0, 0x0FFF) | 0x4000);
        $uuidV4Variant = ($fn(0, 0x3FFF) | 0x8000);

        $block5 = $fn(0, 0xFFFFFFFFFFFF);

        $uuid = sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            $block1,
            $block2,
            $uuidV4Version,
            $uuidV4Variant,
            $block5
        );

        return $uuid;
    }

    protected function fnUuidV5(string $namespaceUuid, string $name) : string
    {
        $namespaceSanitized = str_replace([ '-', '{', '}', '[', ']' ], '', $namespaceUuid);
        $namespaceSanitized = strtolower($namespaceSanitized);

        $binaryNamespace = pack('H*', $namespaceSanitized);

        $hash = hash('sha1', $binaryNamespace . $name, true);

        $block1 = unpack('N', substr($hash, 0, 4))[1];
        $block2 = unpack('n', substr($hash, 4, 2))[1];
        $block3 = unpack('n', substr($hash, 6, 2))[1];
        $block4 = unpack('n', substr($hash, 8, 2))[1];

        $block5Part1 = unpack('n', substr($hash, 10, 2))[1];
        $block5Part2 = unpack('N', substr($hash, 12, 4))[1];

        $uuidV5Version = (($block3 & 0x0FFF) | 0x5000);
        $uuidV5Variant = (($block4 & 0x3FFF) | 0x8000);

        $uuid = sprintf(
            '%08x-%04x-%04x-%04x-%04x%08x',
            $block1,
            $block2,
            $uuidV5Version,
            $uuidV5Variant,
            $block5Part1,
            $block5Part2
        );

        return $uuid;
    }

    protected function fnUuidV7() : string
    {
        $fn = 'random_int';

        $timestamp = (int) (microtime(true) * 1000);

        $block1 = (($timestamp >> 16) & 0xFFFFFFFF);
        $block2 = ($timestamp & 0xFFFF);

        $uuidV7Version = ($fn(0, 0x0FFF) | 0x7000);
        $uuidV7Variant = ($fn(0, 0x3FFF) | 0x8000);

        $block5 = $fn(0, 0xFFFFFFFFFFFF);

        $uuid = sprintf(
            '%08x-%04x-%04x-%04x-%012x',
            $block1,
            $block2,
            $uuidV7Version,
            $uuidV7Variant,
            $block5
        );

        return $uuid;
    }


    /**
     * > числовой id, который после достижения лимита int начинает использовать bcmath() для увеличения на единицу, всегда возвращает строку
     *
     * @param string $refValue
     */
    public function id_increment($value, &$refValue = null) : string
    {
        $value = $value ?? 0;

        $val = $value;

        if ( is_int($val) ) {
            if ( $val === PHP_INT_MAX ) {
                Lib::bcmath();

                $valString = bcadd($val, 1);

            } else {
                $val++;

                $valString = (string) $val;
            }

        } elseif ( is_numeric($val) ) {
            Lib::bcmath();

            $valString = bcadd($val, 1);

        } else {
            throw new LogicException(
                [ 'The `value` should be int or numeric', $value ]
            );
        }

        $refValue = $valString;

        return $valString;
    }

    /**
     * > числовой id, который после достижения лимита int начинает использовать bcmath() для увеличения на единицу, всегда возвращает строку
     *
     * @param string $refValue
     */
    public function id_decrement($value, &$refValue = null) : string
    {
        $value = $value ?? 0;

        $val = $value;

        if ( is_int($val) ) {
            if ( $val === PHP_INT_MIN ) {
                Lib::bcmath();

                $valString = bcsub($val, 1);

            } else {
                $val--;

                $valString = (string) $val;
            }

        } elseif ( is_numeric($val) ) {
            Lib::bcmath();

            $valString = bcsub($val, 1);

        } else {
            throw new LogicException(
                [ 'The `value` should be int or numeric', $value ]
            );
        }

        $refValue = $valString;

        return $valString;
    }
}
