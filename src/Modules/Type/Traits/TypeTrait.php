<?php

namespace Gzhegow\Lib\Modules\Type\Traits;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Nil;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Modules\Net\SubnetV4;
use Gzhegow\Lib\Modules\Net\SubnetV6;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Modules\Bcmath\Number;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Modules\Net\AddressIpV4;
use Gzhegow\Lib\Modules\Net\AddressIpV6;


trait TypeTrait
{
    /**
     * @return Ret<mixed>
     */
    public function empty($value)
    {
        return Lib::php()->type_empty($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_empty($value)
    {
        return Lib::php()->type_any_not_empty($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     *
     * @return Ret<string|array|\Countable|null>
     */
    public function blank($value)
    {
        return Lib::php()->type_blank($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_blank($value)
    {
        return Lib::php()->type_any_not_blank($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     *
     * @return Ret<mixed>
     */
    public function nullable($value)
    {
        return Lib::php()->type_nullable($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_nullable($value)
    {
        return Lib::php()->type_any_not_nullable($value);
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из PHP
     *
     * @return Ret<mixed>
     */
    public function passed($value)
    {
        return Lib::php()->type_passed($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_passed($value)
    {
        return Lib::php()->type_any_not_passed($value);
    }


    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     *
     * > NAN не равен ничему даже самому себе
     * > NIL равен только самому себе
     * > NULL означает пустоту и им можно заменить значения '', [], `resource (closed)`, NIL, но нельзя заменить NAN
     *
     * @return Ret<string|Nil>
     */
    public function nil($value)
    {
        return Lib::php()->type_nil($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_nil($value)
    {
        return Lib::php()->type_any_not_nil($value);
    }


    /**
     * @return Ret<null>
     */
    public function null($value)
    {
        return Lib::php()->type_null($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_null($value)
    {
        return Lib::php()->type_any_not_null($value);
    }


    /**
     * @return Ret<false>
     */
    public function false($value)
    {
        return Lib::php()->type_false($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_false($value)
    {
        return Lib::php()->type_any_not_false($value);
    }


    /**
     * @return Ret<true>
     */
    public function true($value)
    {
        return Lib::php()->type_true($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_true($value)
    {
        return Lib::php()->type_any_not_true($value);
    }


    /**
     * @return Ret<bool>
     */
    public function bool($value)
    {
        return Lib::php()->type_bool($value);
    }

    /**
     * @return Ret<false>
     */
    public function boolfalse($value)
    {
        return Lib::php()->type_boolfalse($value);
    }

    /**
     * @return Ret<true>
     */
    public function booltrue($value)
    {
        return Lib::php()->type_booltrue($value);
    }


    /**
     * @return Ret<bool>
     */
    public function userbool($value)
    {
        return Lib::php()->type_userbool($value);
    }

    /**
     * @return Ret<false>
     */
    public function userfalse($value)
    {
        return Lib::php()->type_userfalse($value);
    }

    /**
     * @return Ret<false>
     */
    public function usertrue($value)
    {
        return Lib::php()->type_usertrue($value);
    }


    /**
     * @return Ret<array>
     */
    public function array($value)
    {
        return Lib::php()->type_array($value);
    }

    /**
     * @return Ret<array>
     */
    public function array_empty($value)
    {
        return Lib::php()->type_array_empty($value);
    }

    /**
     * @return Ret<array>
     */
    public function array_not_empty($value)
    {
        return Lib::php()->type_array_not_empty($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_array_empty($value)
    {
        return Lib::php()->type_any_not_array_empty($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_array($value)
    {
        return Lib::php()->type_any_not_array($value);
    }


    /**
     * @return Ret<object>
     */
    public function object($value)
    {
        return Lib::php()->type_object($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_object($value)
    {
        return Lib::php()->type_any_not_object($value);
    }


    /**
     * @return Ret<\stdClass>
     */
    public function stdclass($value)
    {
        return Lib::php()->type_stdclass($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_stdclass($value)
    {
        return Lib::php()->type_any_not_stdclass($value);
    }


    /**
     * @return Ret<float>
     */
    public function nan($value)
    {
        return Lib::num()->type_nan($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_not_nan($value)
    {
        return Lib::num()->type_float_not_nan($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_nan($value)
    {
        return Lib::num()->type_any_not_nan($value);
    }


    /**
     * @return Ret<float>
     */
    public function finite($value)
    {
        return Lib::num()->type_finite($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_not_finite($value)
    {
        return Lib::num()->type_float_not_finite($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_finite($value)
    {
        return Lib::num()->type_any_not_finite($value);
    }


    /**
     * @return Ret<float>
     */
    public function infinite($value)
    {
        return Lib::num()->type_infinite($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_not_infinite($value)
    {
        return Lib::num()->type_float_not_infinite($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_infinite($value)
    {
        return Lib::num()->type_any_not_infinite($value);
    }


    /**
     * @return Ret<float>
     */
    public function float_min($value)
    {
        return Lib::num()->type_float_min($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_not_float_min($value)
    {
        return Lib::num()->type_float_not_float_min($value);
    }

    /**
     * @return Ret<mixed>
     */
    public function any_not_float_min($value)
    {
        return Lib::num()->type_any_not_float_min($value);
    }


    /**
     * @return Ret<Number>
     */
    public function number($value, ?bool $isAllowExp = null)
    {
        return Lib::num()->type_number($value, $isAllowExp);
    }


    /**
     * @return Ret<string>
     */
    public function numeric($value, ?bool $isAllowExp = null, array $refs = [])
    {
        return Lib::num()->type_numeric($value, $isAllowExp, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_non_zero($value, ?bool $isAllowExp = null, array $refs = [])
    {
        return Lib::num()->type_numeric_non_zero($value, $isAllowExp, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_non_negative($value, ?bool $isAllowExp = null, array $refs = [])
    {
        return Lib::num()->type_numeric_non_negative($value, $isAllowExp, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_non_positive($value, ?bool $isAllowExp = null, array $refs = [])
    {
        return Lib::num()->type_numeric_non_positive($value, $isAllowExp, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_negative($value, ?bool $isAllowExp = null, array $refs = [])
    {
        return Lib::num()->type_numeric_negative($value, $isAllowExp, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_positive($value, ?bool $isAllowExp = null, array $refs = [])
    {
        return Lib::num()->type_numeric_positive($value, $isAllowExp, $refs);
    }


    /**
     * @return Ret<string>
     */
    public function numeric_int($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_non_zero($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_non_zero($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_non_negative($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_non_negative($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_non_positive($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_non_positive($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_negative($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_negative($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_positive($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_positive($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_positive_or_minus_one($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_positive_or_minus_one($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_int_non_negative_or_minus_one($value, array $refs = [])
    {
        return Lib::num()->type_numeric_int_non_negative_or_minus_one($value, $refs);
    }


    /**
     * @return Ret<string>
     */
    public function numeric_float($value, array $refs = [])
    {
        return Lib::num()->type_numeric_float($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_float_non_zero($value, array $refs = [])
    {
        return Lib::num()->type_numeric_float_non_zero($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_float_non_negative($value, array $refs = [])
    {
        return Lib::num()->type_numeric_float_non_negative($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_float_non_positive($value, array $refs = [])
    {
        return Lib::num()->type_numeric_float_non_positive($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_float_negative($value, array $refs = [])
    {
        return Lib::num()->type_numeric_float_negative($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_float_positive($value, array $refs = [])
    {
        return Lib::num()->type_numeric_float_positive($value, $refs);
    }


    /**
     * @return Ret<string>
     */
    public function numeric_trimpad($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        return Lib::num()->type_numeric_trimpad($value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_trimpad_non_zero($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        return Lib::num()->type_numeric_trimpad_non_zero($value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_trimpad_non_negative($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        return Lib::num()->type_numeric_trimpad_non_negative($value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_trimpad_non_positive($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        return Lib::num()->type_numeric_trimpad_non_positive($value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_trimpad_negative($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        return Lib::num()->type_numeric_trimpad_negative($value, $lenTrim, $lenPad, $stringPad, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function numeric_trimpad_positive($value, ?int $lenTrim = null, ?int $lenPad = null, string $stringPad = '0', array $refs = [])
    {
        return Lib::num()->type_numeric_trimpad_positive($value, $lenTrim, $lenPad, $stringPad, $refs);
    }


    /**
     * @return Ret<string>
     */
    public function decimal($value, int $scale = 0, array $refs = [])
    {
        return Lib::num()->type_decimal($value, $scale, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function decimal_non_zero($value, int $scale = 0, array $refs = [])
    {
        return Lib::num()->type_decimal_non_zero($value, $scale, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function decimal_non_negative($value, int $scale = 0, array $refs = [])
    {
        return Lib::num()->type_decimal_non_negative($value, $scale, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function decimal_non_positive($value, int $scale = 0, array $refs = [])
    {
        return Lib::num()->type_decimal_non_positive($value, $scale, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function decimal_negative($value, int $scale = 0, array $refs = [])
    {
        return Lib::num()->type_decimal_negative($value, $scale, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function decimal_positive($value, int $scale = 0, array $refs = [])
    {
        return Lib::num()->type_decimal_positive($value, $scale, $refs);
    }


    /**
     * @return Ret<int|float>
     */
    public function num($value)
    {
        return Lib::num()->type_num($value);
    }

    /**
     * @return Ret<int|float>
     */
    public function num_non_zero($value)
    {
        return Lib::num()->type_num_non_zero($value);
    }

    /**
     * @return Ret<int|float>
     */
    public function num_non_negative($value)
    {
        return Lib::num()->type_num_non_negative($value);
    }

    /**
     * @return Ret<int|float>
     */
    public function num_non_positive($value)
    {
        return Lib::num()->type_num_non_positive($value);
    }

    /**
     * @return Ret<int|float>
     */
    public function num_negative($value)
    {
        return Lib::num()->type_num_negative($value);
    }

    /**
     * @return Ret<int|float>
     */
    public function num_positive($value)
    {
        return Lib::num()->type_num_positive($value);
    }


    /**
     * @return Ret<int>
     */
    public function int($value)
    {
        return Lib::num()->type_int($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_non_zero($value)
    {
        return Lib::num()->type_int_non_zero($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_non_negative($value)
    {
        return Lib::num()->type_int_non_negative($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_non_positive($value)
    {
        return Lib::num()->type_int_non_positive($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_negative($value)
    {
        return Lib::num()->type_int_negative($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_positive($value)
    {
        return Lib::num()->type_int_positive($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_positive_or_minus_one($value)
    {
        return Lib::num()->type_int_positive_or_minus_one($value);
    }

    /**
     * @return Ret<int>
     */
    public function int_non_negative_or_minus_one($value)
    {
        return Lib::num()->type_int_non_negative_or_minus_one($value);
    }


    /**
     * @return Ret<float>
     */
    public function float($value)
    {
        return Lib::num()->type_float($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_non_zero($value)
    {
        return Lib::num()->type_float_non_zero($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_non_negative($value)
    {
        return Lib::num()->type_float_non_negative($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_non_positive($value)
    {
        return Lib::num()->type_float_non_positive($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_negative($value)
    {
        return Lib::num()->type_float_negative($value);
    }

    /**
     * @return Ret<float>
     */
    public function float_positive($value)
    {
        return Lib::num()->type_float_positive($value);
    }


    /**
     * @return Ret<Bcnumber>
     */
    public function bcnumber($value)
    {
        return Lib::bcmath()->type_bcnumber($value);
    }


    /**
     * @return Ret<string>
     */
    public function a_string($value)
    {
        return Lib::str()->type_a_string($value);
    }

    /**
     * @return Ret<string>
     */
    public function a_string_empty($value)
    {
        return Lib::str()->type_a_string_empty($value);
    }

    /**
     * @return Ret<string>
     */
    public function a_string_not_empty($value)
    {
        return Lib::str()->type_a_string_not_empty($value);
    }

    /**
     * @return Ret<string>
     */
    public function a_trim($value)
    {
        return Lib::str()->type_a_trim($value);
    }


    /**
     * @return Ret<string>
     */
    public function string($value)
    {
        return Lib::str()->type_string($value);
    }

    /**
     * @return Ret<string>
     */
    public function string_empty($value)
    {
        return Lib::str()->type_string_empty($value);
    }

    /**
     * @return Ret<string>
     */
    public function string_not_empty($value)
    {
        return Lib::str()->type_string_not_empty($value);
    }

    /**
     * @return Ret<string>
     */
    public function trim($value, ?string $characters = null)
    {
        return Lib::str()->type_trim($value, $characters);
    }


    /**
     * @return Ret<string>
     */
    public function char($value)
    {
        return Lib::str()->type_char($value);
    }

    /**
     * @return Ret<string>
     */
    public function letter($value)
    {
        return Lib::str()->type_letter($value);
    }

    /**
     * @return Ret<string>
     */
    public function word($value)
    {
        return Lib::str()->type_word($value);
    }

    /**
     * @return Ret<Alphabet>
     */
    public function alphabet($value)
    {
        return Lib::str()->type_alphabet($value);
    }


    /**
     * @return Ret<string>
     */
    public function ctype_digit($value)
    {
        return Lib::str()->type_ctype_digit($value);
    }

    /**
     * @return Ret<string>
     */
    public function ctype_alpha($value, ?bool $allowUpperCase = null)
    {
        return Lib::str()->type_ctype_alpha($value, $allowUpperCase);
    }

    /**
     * @return Ret<string>
     */
    public function ctype_alnum($value, ?bool $allowUpperCase = null)
    {
        return Lib::str()->type_ctype_alnum($value, $allowUpperCase);
    }


    /**
     * @return Ret<string>
     */
    public function base($value, $alphabet)
    {
        return Lib::crypt()->type_base($value, $alphabet);
    }

    /**
     * @return Ret<string>
     */
    public function base_bin($value)
    {
        return Lib::crypt()->type_base_bin($value);
    }

    /**
     * @return Ret<string>
     */
    public function base_oct($value)
    {
        return Lib::crypt()->type_base_oct($value);
    }

    /**
     * @return Ret<string>
     */
    public function base_dec($value)
    {
        return Lib::crypt()->type_base_dec($value);
    }

    /**
     * @return Ret<string>
     */
    public function base_hex($value)
    {
        return Lib::crypt()->type_base_hex($value);
    }


    /**
     * @return Ret<int|string>
     */
    public function key($key)
    {
        return Lib::arr()->type_key($key);
    }

    /**
     * @return Ret<mixed>
     */
    public function key_exists($key, array $array)
    {
        return Lib::arr()->type_key_exists($key, $array);
    }

    /**
     * @return Ret<null>
     */
    public function key_not_exists($key, array $array)
    {
        return Lib::arr()->type_key_not_exists($key, $array);
    }


    /**
     * @return Ret<array>
     */
    public function array_plain($value, ?int $maxDepth = null)
    {
        return Lib::arr()->type_array_plain($value, $maxDepth);
    }


    /**
     * @return Ret<array>
     */
    public function list($value, ?int $plainMaxDepth = null)
    {
        return Lib::arr()->type_list($value, $plainMaxDepth);
    }

    /**
     * @return Ret<array>
     */
    public function list_sorted($value, ?int $plainMaxDepth = null)
    {
        return Lib::arr()->type_list_sorted($value, $plainMaxDepth);
    }


    /**
     * @return Ret<array>
     */
    public function dict($value, ?int $plainMaxDepth = null)
    {
        return Lib::arr()->type_dict($value, $plainMaxDepth);
    }

    /**
     * @return Ret<array>
     */
    public function dict_sorted($value, ?int $plainMaxDepth = null, $fnSortCmp = null)
    {
        return Lib::arr()->type_dict_sorted($value, $plainMaxDepth, $fnSortCmp);
    }


    /**
     * @return Ret<array>
     */
    public function table($value)
    {
        return Lib::arr()->type_table($value);
    }

    /**
     * @return Ret<array>
     */
    public function matrix($value)
    {
        return Lib::arr()->type_matrix($value);
    }

    /**
     * @return Ret<array>
     */
    public function matrix_strict($value)
    {
        return Lib::arr()->type_matrix_strict($value);
    }


    /**
     * @return Ret<ArrPath>
     */
    public function arrpath($path)
    {
        return Lib::arr()->type_arrpath($path);
    }

    /**
     * @return Ret<ArrPath>
     */
    public function arrpath_dot($path, ?string $dot = '.')
    {
        return Lib::arr()->type_arrpath_dot($path, $dot);
    }


    /**
     * @return Ret<array>
     */
    public function array_of_type($value, string $type)
    {
        return Lib::arr()->type_array_of_type($value, $type);
    }

    /**
     * @return Ret<resource[]>
     */
    public function array_of_resource_type($value, string $resourceType)
    {
        return Lib::arr()->type_array_of_resource_type($value, $resourceType);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>
     */
    public function array_of_a($value, string $className)
    {
        return Lib::arr()->type_array_of_a($value, $className);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>
     */
    public function array_of_class($value, string $className)
    {
        return Lib::arr()->type_array_of_class($value, $className);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>
     */
    public function array_of_subclass($value, string $className)
    {
        return Lib::arr()->type_array_of_subclass($value, $className);
    }

    /**
     * @param callable $fn
     *
     * @return Ret<array>
     *
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    public function array_of_callback($value, callable $fn, array $args = [])
    {
        return Lib::arr()->type_array_of_callback($value, $fn, $args);
    }


    /**
     * @return Ret<array{ 0: string, 1: int }>
     */
    public function fileline($value)
    {
        return Lib::debug()->type_fileline($value);
    }


    /**
     * @return Ret<string>
     */
    public function html_tag($value)
    {
        return Lib::format()->type_html_tag($value);
    }

    /**
     * @return Ret<string>
     */
    public function xml_tag($value)
    {
        return Lib::format()->type_xml_tag($value);
    }

    /**
     * @return Ret<string>
     */
    public function xml_nstag($value)
    {
        return Lib::format()->type_xml_nstag($value);
    }


    /**
     * @return Ret<string>
     */
    public function regex($value)
    {
        return Lib::preg()->type_regex($value);
    }

    /**
     * @return Ret<string>
     */
    public function regexp($value)
    {
        return Lib::preg()->type_regexp($value);
    }


    /**
     * @return Ret<AddressIpV4|AddressIpV6>
     */
    public function address_ip($value)
    {
        return Lib::net()->type_address_ip($value);
    }

    /**
     * @return Ret<AddressIpV4>
     */
    public function address_ip_v4($value)
    {
        return Lib::net()->type_address_ip_v4($value);
    }

    /**
     * @return Ret<AddressIpV6>
     */
    public function address_ip_v6($value)
    {
        return Lib::net()->type_address_ip_v6($value);
    }

    /**
     * @return Ret<string>
     */
    public function address_mac($value)
    {
        return Lib::net()->type_address_mac($value);
    }


    /**
     * @return Ret<SubnetV4|SubnetV6>
     */
    public function subnet($value, ?string $ipFallback = null)
    {
        return Lib::net()->type_subnet($value, $ipFallback);
    }

    /**
     * @return Ret<SubnetV4>
     */
    public function subnet_v4($value, ?string $ipFallback = null)
    {
        return Lib::net()->type_subnet_v4($value, $ipFallback);
    }

    /**
     * @return Ret<SubnetV6>
     */
    public function subnet_v6($value, ?string $ipFallback = null)
    {
        return Lib::net()->type_subnet_v6($value, $ipFallback);
    }


    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function url($value, $query = null, $fragment = null, ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null, array $refs = [])
    {
        return Lib::url()->type_url($value, $query, $fragment, $isHostIdnaAscii, $isLinkUrlencoded, $refs);
    }

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function uri($value, $query = null, $fragment = null, ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null, array $refs = [])
    {
        return Lib::url()->type_uri($value, $query, $fragment, $isHostIdnaAscii, $isLinkUrlencoded, $refs);
    }

    /**
     * @param string|true $value
     *
     * @return Ret<string>
     */
    public function host($value, ?int $isHostIdnaAscii = null, array $refs = [])
    {
        return Lib::url()->type_host($value, $isHostIdnaAscii, $refs);
    }

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function link($value, $query = null, $fragment = null, ?int $isLinkUrlencoded = null, array $refs = [])
    {
        return Lib::url()->type_link($value, $query, $fragment, $isLinkUrlencoded, $refs);
    }


    /**
     * @param string $value
     *
     * @return Ret<string>
     */
    public function dsn_pdo($value, array $refs = [])
    {
        return Lib::url()->type_dsn_pdo($value, $refs);
    }


    /**
     * @return Ret<string>
     */
    public function uuid($value)
    {
        return Lib::random()->type_uuid($value);
    }


    /**
     * @return Ret<array|\Countable>
     */
    public function countable($value)
    {
        return Lib::php()->type_countable($value);
    }

    /**
     * @return Ret<\Countable>
     */
    public function countable_object($value)
    {
        return Lib::php()->type_countable_object($value);
    }

    /**
     * @return Ret<string|array|\Countable>
     */
    public function sizeable($value)
    {
        return Lib::php()->type_sizeable($value);
    }


    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone($timezone, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_timezone($timezone, $allowedTimezoneTypes);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_offset($timezoneOrOffset)
    {
        return Lib::date()->type_timezone_offset($timezoneOrOffset);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_abbr($timezoneOrAbbr)
    {
        return Lib::date()->type_timezone_abbr($timezoneOrAbbr);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_name($timezoneOrName)
    {
        return Lib::date()->type_timezone_name($timezoneOrName);
    }

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_nameabbr($timezoneOrNameOrAbbr)
    {
        return Lib::date()->type_timezone_nameabbr($timezoneOrNameOrAbbr);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date($datestring, $timezoneFallback = null)
    {
        return Lib::date()->type_date($datestring, $timezoneFallback);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function adate($datestring, $timezoneFallback = null)
    {
        return Lib::date()->type_adate($datestring, $timezoneFallback);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate($datestring, $timezoneFallback = null)
    {
        return Lib::date()->type_idate($datestring, $timezoneFallback);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        return Lib::date()->type_date_formatted($dateFormatted, $formats, $timezoneFallback);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function adate_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        return Lib::date()->type_adate_formatted($dateFormatted, $formats, $timezoneFallback);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_formatted($dateFormatted, $formats, $timezoneFallback = null)
    {
        return Lib::date()->type_idate_formatted($dateFormatted, $formats, $timezoneFallback);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_tz($datestring, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_date_tz($datestring, $allowedTimezoneTypes);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function adate_tz($datestring, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_adate_tz($datestring, $allowedTimezoneTypes);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_tz($datestring, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_idate_tz($datestring, $allowedTimezoneTypes);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_date_tz_formatted($dateFormatted, $formats, $allowedTimezoneTypes);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function adate_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_adate_tz_formatted($dateFormatted, $formats, $allowedTimezoneTypes);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null)
    {
        return Lib::date()->type_idate_tz_formatted($dateFormatted, $formats, $allowedTimezoneTypes);
    }


    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_microtime($microtime, $timezoneFallback = null)
    {
        return Lib::date()->type_date_microtime($microtime, $timezoneFallback);
    }

    /**
     * @return Ret<\DateTime>
     */
    public function adate_microtime($microtime, $timezoneFallback = null)
    {
        return Lib::date()->type_adate_microtime($microtime, $timezoneFallback);
    }

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_microtime($microtime, $timezoneFallback = null)
    {
        return Lib::date()->type_idate_microtime($microtime, $timezoneFallback);
    }


    /**
     * @return Ret<\DateInterval>
     */
    public function interval($interval)
    {
        return Lib::date()->type_interval($interval);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_duration($duration)
    {
        return Lib::date()->type_interval_duration($duration);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_datestring($datestring)
    {
        return Lib::date()->type_interval_datestring($datestring);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_microtime($microtime)
    {
        return Lib::date()->type_interval_microtime($microtime);
    }

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_ago($date, ?\DateTimeInterface $from = null, ?bool $reverse = null)
    {
        return Lib::date()->type_interval_ago($date, $from, $reverse);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function struct_exists($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_exists($value, $flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function struct($value, ?int $flags = null)
    {
        return Lib::php()->type_struct($value, $flags);
    }

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function struct_class($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_class($value, $flags);
    }

    /**
     * @return Ret<class-string>
     */
    public function struct_interface($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_interface($value, $flags);
    }

    /**
     * @return Ret<class-string>
     */
    public function struct_trait($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_trait($value, $flags);
    }

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function struct_enum($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_enum($value, $flags);
    }


    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>
     */
    public function struct_fqcn($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_fqcn($value, $flags);
    }

    /**
     * @return Ret<string>
     */
    public function struct_namespace($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_namespace($value, $flags);
    }

    /**
     * @return Ret<string>
     */
    public function struct_basename($value, ?int $flags = null)
    {
        return Lib::php()->type_struct_basename($value, $flags);
    }


    /**
     * @return Ret<resource>
     */
    public function resource($value, ?string $resourceType = null)
    {
        return Lib::php()->type_resource($value, $resourceType);
    }

    /**
     * @return Ret<resource>
     */
    public function resource_opened($value, ?string $resourceType = null)
    {
        return Lib::php()->type_resource_opened($value, $resourceType);
    }

    /**
     * @return Ret<resource>
     */
    public function resource_closed($value)
    {
        return Lib::php()->type_resource_closed($value);
    }

    /**
     * @return Ret<resource>
     */
    public function any_not_resource($value)
    {
        return Lib::php()->type_any_not_resource($value);
    }


    /**
     * @return Ret<resource|\CurlHandle>
     */
    public function curl($value)
    {
        return Lib::php()->type_curl($value);
    }


    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     *
     * @return Ret<T>
     */
    public function enum_case($value, ?string $enumClass = null)
    {
        return Lib::php()->type_enum_case($value, $enumClass);
    }


    /**
     * > метод не всегда callable, поскольку строка 'class->method' не является callable
     * > метод не всегда callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_string, если собираетесь вызывать метод
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>
     */
    public function method($value, array $refs = [])
    {
        return Lib::php()->type_method($value, $refs);
    }

    /**
     * @return Ret<array{ 0: class-string, 1: string }>
     */
    public function method_array($value)
    {
        return Lib::php()->type_method_array($value);
    }

    /**
     * @return Ret<string>
     */
    public function method_string($value)
    {
        return Lib::php()->type_method_string($value);
    }


    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>
     */
    public function callable($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_object($value, $newScope);
    }

    /**
     * @return Ret<callable|\Closure|object>
     */
    public function callable_object($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_object($value, $newScope);
    }

    /**
     * @return Ret<\Closure>
     */
    public function callable_object_closure($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_object_closure($value, $newScope);
    }

    /**
     * @return Ret<callable|object>
     */
    public function callable_object_invokable($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_object_invokable($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function callable_array($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_array($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function callable_array_method($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_array_method($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>
     */
    public function callable_array_method_static($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_array_method_static($value, $newScope);
    }

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>
     */
    public function callable_array_method_non_static($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_array_method_non_static($value, $newScope);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function callable_string($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_string($value, $newScope);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function callable_string_function($value)
    {
        return Lib::php()->type_callable_string_function($value);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function callable_string_function_internal($value)
    {
        return Lib::php()->type_callable_string_function_internal($value);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function callable_string_function_non_internal($value)
    {
        return Lib::php()->type_callable_string_function_non_internal($value);
    }

    /**
     * @return Ret<callable|callable-string>
     */
    public function callable_string_method_static($value, $newScope = 'static')
    {
        return Lib::php()->type_callable_string_method_static($value, $newScope);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function path($value, array $refs = [])
    {
        return Lib::php()->type_path($value, $refs);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function path_normalized($value, ?string $separator = null, array $refs = [])
    {
        return Lib::php()->type_path_normalized($value, $separator, $refs);
    }


    /**
     * @return Ret<int>
     */
    public function chmod($value)
    {
        return Lib::fs()->type_chmod($value);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function realpath($value, ?bool $isAllowSymlink = null, array $refs = [])
    {
        return Lib::fs()->type_realpath($value, $isAllowSymlink, $refs);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function freepath($value, array $refs = [])
    {
        return Lib::fs()->type_freepath($value, $refs);
    }

    /**
     * @return Ret<string>
     */
    public function freepath_normalized($value, ?string $separator = null, array $refs = [])
    {
        return Lib::fs()->type_freepath_normalized($value, $separator, $refs);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function dirpath($value, ?bool $isAllowExists, ?bool $isAllowSymlink = null, array $refs = [])
    {
        return Lib::fs()->type_dirpath($value, $isAllowExists, $isAllowSymlink, $refs);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function filepath($value, ?bool $isAllowExists, ?bool $isAllowSymlink = null, array $refs = [])
    {
        return Lib::fs()->type_filepath($value, $isAllowExists, $isAllowSymlink, $refs);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function dirpath_realpath($value, ?bool $isAllowSymlink = null, array $refs = [])
    {
        return Lib::fs()->type_dirpath_realpath($value, $isAllowSymlink, $refs);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function filepath_realpath($value, ?bool $isAllowSymlink = null, array $refs = [])
    {
        return Lib::fs()->type_filepath_realpath($value, $isAllowSymlink, $refs);
    }


    /**
     * @return Ret<string>
     */
    public function filename($value)
    {
        return Lib::fs()->type_filename($value);
    }


    /**
     * @return Ret<\SplFileInfo>
     */
    public function file($value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null)
    {
        return Lib::fs()->type_file($value, $extensions, $mimeTypes, $filters);
    }

    /**
     * @return Ret<\SplFileInfo>
     */
    public function image($value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null)
    {
        return Lib::fs()->type_image($value, $extensions, $mimeTypes, $filters);
    }


    /**
     * @return Ret<resource|\Socket>
     */
    public function socket($value)
    {
        return Lib::fs()->type_socket($value);
    }

    /**
     * @return Ret<resource>
     */
    public function stream($value)
    {
        return Lib::fs()->type_stream($value);
    }


    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email($value, ?array $filters = null, array $refs = [])
    {
        return Lib::social()->type_email($value, $filters, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email_fake($value, array $refs = [])
    {
        return Lib::social()->type_email_fake($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email_non_fake($value, ?array $filters = null, array $refs = [])
    {
        return Lib::social()->type_email_non_fake($value, $filters, $refs);
    }


    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone($value, array $refs = [])
    {
        return Lib::social()->type_phone($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_fake($value, array $refs = [])
    {
        return Lib::social()->type_phone_fake($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_non_fake($value, array $refs = [])
    {
        return Lib::social()->type_phone_non_fake($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string, 3?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_real($value, ?string $region = '', array $refs = [])
    {
        return Lib::social()->type_phone_real($value, $region, $refs);
    }


    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel($value, array $refs = [])
    {
        return Lib::social()->type_tel($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_fake($value, array $refs = [])
    {
        return Lib::social()->type_tel_fake($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_non_fake($value, array $refs = [])
    {
        return Lib::social()->type_tel_non_fake($value, $refs);
    }

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_real($value, ?string $region = '', array $refs = [])
    {
        return Lib::social()->type_tel_real($value, $region, $refs);
    }
}
