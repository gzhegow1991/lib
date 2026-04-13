<?php

/**
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace Gzhegow\Lib\Modules\Type\Interfaces;

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


interface TInterface
{
    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     *
     * @return Ret<string|array|\Countable|null>|string|array|\Countable|null
     */
    public function blank($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_blank($fb, $value);

    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     *
     * @return Ret<mixed>|mixed
     */
    public function nullable($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_nullable($fb, $value);

    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из PHP
     *
     * @return Ret<mixed>|mixed
     */
    public function client($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_client($fb, $value);

    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     *
     * > NAN не равен ничему даже самому себе
     * > NIL равен только самому себе
     * > NULL означает пустоту и им можно заменить значения '', [], `resource (closed)`, NIL, но нельзя заменить NAN
     *
     * @return Ret<string|Nil>|string|Nil
     */
    public function nil($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_nil($fb, $value);

    /**
     * @return Ret<null>|null
     */
    public function null($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_null($fb, $value);

    /**
     * @return Ret<bool>|bool
     */
    public function php_bool($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_php_bool($fb, $value);

    /**
     * @return Ret<false>|false
     */
    public function php_bool_false($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_php_bool_false($fb, $value);

    /**
     * @return Ret<true>|true
     */
    public function php_bool_true($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_php_bool_true($fb, $value);

    /**
     * @return Ret<bool>|bool
     */
    public function bool($fb, $value);

    /**
     * @return Ret<false>|false
     */
    public function bool_false($fb, $value);

    /**
     * @return Ret<true>|true
     */
    public function bool_true($fb, $value);

    /**
     * @return Ret<bool>|bool
     */
    public function userbool($fb, $value);

    /**
     * @return Ret<false>|false
     */
    public function userbool_false($fb, $value);

    /**
     * @return Ret<false>|false
     */
    public function userbool_true($fb, $value);

    /**
     * @return Ret<array>|array
     */
    public function array($fb, $value);

    /**
     * @return Ret<array>|array
     */
    public function array_empty($fb, $value);

    /**
     * @return Ret<array>|array
     */
    public function array_not_empty($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_array($fb, $value);

    /**
     * @return Ret<object>|object
     */
    public function object($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_object($fb, $value);

    /**
     * @return Ret<\stdClass>|\stdClass
     */
    public function stdclass($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_stdclass($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function php_int($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function php_float($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function nan($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_not_nan($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_maybe_nan($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_nan($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function finite($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_not_finite($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_finite($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function infinite($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_not_infinite($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_infinite($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_min($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_not_float_min($fb, $value);

    /**
     * @return Ret<mixed>|mixed
     */
    public function any_not_float_min($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function numeric($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_int($fb, $value, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_float($fb, $value, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function decimal($fb, $value, int $scale = 0, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_non_zero($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_non_negative($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_non_positive($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_negative($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_positive($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_non_negative_or_minus_one($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_positive_or_minus_one($fb, $value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_gt($fb, $value, $gt, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_gte($fb, $value, $gte, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_lt($fb, $value, $lt, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_lte($fb, $value, $lte, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_between($fb, $value, $from, $to, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function numeric_inside($fb, $value, $from, $to, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_non_zero($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_non_negative($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_non_positive($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_negative($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_positive($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_non_negative_or_minus_one($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_positive_or_minus_one($fb, $value);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_gt($fb, $value, $gt);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_gte($fb, $value, $gte);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_lt($fb, $value, $lt);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_lte($fb, $value, $lte);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_between($fb, $value, $from, $to);

    /**
     * @return Ret<int|float>|int|float
     */
    public function num_inside($fb, $value, $from, $to);

    /**
     * @return Ret<int>|int
     */
    public function int_non_zero($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_non_negative($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_non_positive($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_negative($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_positive($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_non_negative_or_minus_one($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_positive_or_minus_one($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function int_gt($fb, $value, $gt);

    /**
     * @return Ret<int>|int
     */
    public function int_gte($fb, $value, $gte);

    /**
     * @return Ret<int>|int
     */
    public function int_lt($fb, $value, $lt);

    /**
     * @return Ret<int>|int
     */
    public function int_lte($fb, $value, $lte);

    /**
     * @return Ret<int>|int
     */
    public function int_between($fb, $value, $from, $to);

    /**
     * @return Ret<int>|int
     */
    public function int_inside($fb, $value, $from, $to);

    /**
     * @return Ret<float>|float
     */
    public function float_non_zero($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_non_negative($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_non_positive($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_negative($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_positive($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_non_negative_or_minus_one($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_positive_or_minus_one($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function float_gt($fb, $value, $gt);

    /**
     * @return Ret<float>|float
     */
    public function float_gte($fb, $value, $gte);

    /**
     * @return Ret<float>|float
     */
    public function float_lt($fb, $value, $lt);

    /**
     * @return Ret<float>|float
     */
    public function float_lte($fb, $value, $lte);

    /**
     * @return Ret<float>|float
     */
    public function float_between($fb, $value, $from, $to);

    /**
     * @return Ret<float>|float
     */
    public function float_inside($fb, $value, $from, $to);

    /**
     * @return Ret<Number>|Number
     */
    public function number($fb, $value, ?bool $isAllowExp = null);

    /**
     * @return Ret<int>|int
     */
    public function exponent($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function scale($fb, $value);

    /**
     * @return Ret<int>|int
     */
    public function percent($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function percent_numeric($fb, $value);

    /**
     * @return Ret<float>|float
     */
    public function ratio($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function ratio_numeric($fb, $value);

    /**
     * @return Ret<Bcnumber>|Bcnumber
     */
    public function bcnumber($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function php_string($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function php_string_empty($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function php_string_not_empty($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function php_trim($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function string($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function string_empty($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function string_not_empty($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function trim($fb, $value, ?string $characters = null);

    /**
     * @return Ret<string>|string
     */
    public function char($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function letter($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function word($fb, $value);

    /**
     * @return Ret<Alphabet>|Alphabet
     */
    public function alphabet($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function ctype_digit($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function ctype_alpha($fb, $value, ?bool $allowUpperCase = null);

    /**
     * @return Ret<string>|string
     */
    public function ctype_alnum($fb, $value, ?bool $allowUpperCase = null);

    /**
     * @return Ret<string>|string
     */
    public function base($fb, $value, $alphabet);

    /**
     * @return Ret<string>|string
     */
    public function base_bin($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function base_oct($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function base_dec($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function base_hex($fb, $value);

    /**
     * @return Ret<int|string>|int|string
     */
    public function key($fb, $key);

    /**
     * @return Ret<mixed>|mixed
     */
    public function key_exists($fb, $key, array $array);

    /**
     * @return Ret<array>|array
     */
    public function keys_exists($fb, $keys, array $array);

    /**
     * @return Ret<array>|array
     */
    public function keys_not_exists($fb, $keys, array $array);

    /**
     * @return Ret<array>|array
     */
    public function array_plain($fb, $value, ?int $maxDepth = null);

    /**
     * @return Ret<array>|array
     */
    public function list($fb, $value, ?int $plainMaxDepth = null);

    /**
     * @return Ret<array>|array
     */
    public function list_sorted($fb, $value, ?int $plainMaxDepth = null);

    /**
     * @return Ret<array>|array
     */
    public function dict($fb, $value, ?int $plainMaxDepth = null);

    /**
     * @return Ret<array>|array
     */
    public function dict_sorted($fb, $value, ?int $plainMaxDepth = null, $fnSortCmp = null);

    /**
     * @return Ret<array>|array
     */
    public function table($fb, $value);

    /**
     * @return Ret<array>|array
     */
    public function matrix($fb, $value);

    /**
     * @return Ret<array>|array
     */
    public function matrix_strict($fb, $value);

    /**
     * @return Ret<ArrPath>|ArrPath
     */
    public function arrpath($fb, $path);

    /**
     * @return Ret<ArrPath>|ArrPath
     */
    public function arrpath_dot($fb, $path, ?string $dot = '.');

    /**
     * @return Ret<array>|array
     */
    public function array_of_type($fb, $value, string $type);

    /**
     * @return Ret<resource[]>|resource[]
     */
    public function array_of_resource_type($fb, $value, string $resourceType);

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>|T[]
     */
    public function array_of_a($fb, $value, string $className);

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>|T[]
     */
    public function array_of_class($fb, $value, string $className);

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>|T[]
     */
    public function array_of_subclass($fb, $value, string $className);

    /**
     * @param callable $fn
     *
     * @return Ret<array>|array
     *
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    public function array_of_callback($fb, $value, callable $fn, array $args = []);

    /**
     * @return Ret<array{ 0: string, 1: int }>|array{ 0: string, 1: int }
     */
    public function fileline($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function html_tag($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function xml_tag($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function xml_nstag($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function regex($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function regexp($fb, $value);

    /**
     * @return Ret<AddressIpV4|AddressIpV6>|AddressIpV4|AddressIpV6
     */
    public function address_ip($fb, $value);

    /**
     * @return Ret<AddressIpV4>|AddressIpV4
     */
    public function address_ip_v4($fb, $value);

    /**
     * @return Ret<AddressIpV6>|AddressIpV6
     */
    public function address_ip_v6($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function address_mac($fb, $value);

    /**
     * @return Ret<SubnetV4|SubnetV6>|SubnetV4|SubnetV6
     */
    public function subnet($fb, $value, ?string $ipFallback = null);

    /**
     * @return Ret<SubnetV4>|SubnetV4
     */
    public function subnet_v4($fb, $value, ?string $ipFallback = null);

    /**
     * @return Ret<SubnetV6>|SubnetV6
     */
    public function subnet_v6($fb, $value, ?string $ipFallback = null);

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>|string
     */
    public function url($fb, $value, $query = null, $fragment = null, ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null, array $refs = []);

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>|string
     */
    public function uri($fb, $value, $query = null, $fragment = null, ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null, array $refs = []);

    /**
     * @param string|true $value
     *
     * @return Ret<string>|string
     */
    public function host($fb, $value, ?int $isHostIdnaAscii = null, array $refs = []);

    /**
     * @param string|true $value
     *
     * @return Ret<string>|string
     */
    public function domain($fb, $value, ?int $isHostIdnaAscii = null, array $refs = []);

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>|string
     */
    public function link($fb, $value, $query = null, $fragment = null, ?int $isLinkUrlencoded = null, array $refs = []);

    /**
     * @param string $value
     *
     * @return Ret<string>|string
     */
    public function dsn_pdo($fb, $value, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function uuid($fb, $value);

    /**
     * @return Ret<array|\Countable>|array|\Countable
     */
    public function countable($fb, $value);

    /**
     * @return Ret<\Countable>|\Countable
     */
    public function countable_object($fb, $value);

    /**
     * @return Ret<array|\Countable>|array|\Countable
     */
    public function sizeable($fb, $value);

    /**
     * @return Ret<\DateTimeZone>|\DateTimeZone
     */
    public function timezone($fb, $timezone, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeZone>|\DateTimeZone
     */
    public function timezone_offset($fb, $timezoneOrOffset);

    /**
     * @return Ret<\DateTimeZone>|\DateTimeZone
     */
    public function timezone_abbr($fb, $timezoneOrAbbr);

    /**
     * @return Ret<\DateTimeZone>|\DateTimeZone
     */
    public function timezone_name($fb, $timezoneOrName);

    /**
     * @return Ret<\DateTimeZone>|\DateTimeZone
     */
    public function timezone_nameabbr($fb, $timezoneOrNameOrAbbr);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date($fb, $datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate($fb, $datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate($fb, $datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date_formatted($fb, $dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate_formatted($fb, $dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate_formatted($fb, $dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date_tz($fb, $datestring, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate_tz($fb, $datestring, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate_tz($fb, $datestring, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date_tz_formatted($fb, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate_tz_formatted($fb, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate_tz_formatted($fb, $dateFormatted, $formats, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date_no_tz($fb, $datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate_no_tz($fb, $datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate_no_tz($fb, $datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date_no_tz_formatted($fb, $dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate_no_tz_formatted($fb, $dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate_no_tz_formatted($fb, $dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>|\DateTimeInterface
     */
    public function date_microtime($fb, $microtime, $timezoneSet = null);

    /**
     * @return Ret<\DateTime>|\DateTime
     */
    public function adate_microtime($fb, $microtime, $timezoneSet = null);

    /**
     * @return Ret<\DateTimeImmutable>|\DateTimeImmutable
     */
    public function idate_microtime($fb, $microtime, $timezoneSet = null);

    /**
     * @return Ret<\DateInterval>|\DateInterval
     */
    public function interval($fb, $interval);

    /**
     * @return Ret<\DateInterval>|\DateInterval
     */
    public function interval_duration($fb, $duration);

    /**
     * @return Ret<\DateInterval>|\DateInterval
     */
    public function interval_datestring($fb, $datestring);

    /**
     * @return Ret<\DateInterval>|\DateInterval
     */
    public function interval_microtime($fb, $microtime);

    /**
     * @return Ret<\DateInterval>|\DateInterval
     */
    public function interval_ago($fb, $date, ?\DateTimeInterface $from = null, ?bool $reverse = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_exists($fb, $value, ?int $flags = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct($fb, $value, ?int $flags = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_class($fb, $value, ?int $flags = null);

    /**
     * @return Ret<class-string>|class-string
     */
    public function struct_interface($fb, $value, ?int $flags = null);

    /**
     * @return Ret<class-string>|class-string
     */
    public function struct_trait($fb, $value, ?int $flags = null);

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_enum($fb, $value, ?int $flags = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_fqcn($fb, $value, ?int $flags = null);

    /**
     * @return Ret<string>|string
     */
    public function struct_namespace($fb, $value, ?int $flags = null);

    /**
     * @return Ret<string>|string
     */
    public function struct_basename($fb, $value, ?int $flags = null);

    /**
     * @return Ret<resource>|resource
     */
    public function resource($fb, $value, ?string $resourceType = null);

    /**
     * @return Ret<resource>|resource
     */
    public function resource_opened($fb, $value, ?string $resourceType = null);

    /**
     * @return Ret<resource>|resource
     */
    public function resource_closed($fb, $value);

    /**
     * @return Ret<resource>|resource
     */
    public function any_not_resource($fb, $value);

    /**
     * @return Ret<resource|\CurlHandle>|resource|\CurlHandle
     */
    public function curl($fb, $value);

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     *
     * @return Ret<T>|T
     */
    public function enum_case($fb, $value, ?string $enumClass = null);

    /**
     * > метод не всегда callable, поскольку строка 'class->method' не является callable
     * > метод не всегда callable, поскольку массив [ 'class', 'method' ] не является callable, если метод публичный
     * > используйте type_callable_string, если собираетесь вызывать метод
     * > используйте type_callable_array, если собираетесь вызывать метод
     *
     * @param array{ 0?: array{ 0: class-string, 1: string }, 1?: string } $refs
     *
     * @return Ret<bool>|bool
     */
    public function method($fb, $value, array $refs = []);

    /**
     * @return Ret<array{ 0: class-string, 1: string }>|array{ 0: class-string, 1: string }
     */
    public function method_array($fb, $value);

    /**
     * @return Ret<string>|string
     */
    public function method_string($fb, $value);

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>|callable
     */
    public function callable($fb, $value, $newScope = 'static');

    /**
     * @return Ret<callable|\Closure|object>|callable|\Closure|object
     */
    public function callable_object($fb, $value, $newScope = 'static');

    /**
     * @return Ret<\Closure>|\Closure
     */
    public function callable_object_closure($fb, $value, $newScope = 'static');

    /**
     * @return Ret<callable|object>|callable|object
     */
    public function callable_object_invokable($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>|callable|array{ 0: object|class-string, 1: string }
     */
    public function callable_array($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>|callable|array{ 0: object|class-string, 1: string }
     */
    public function callable_array_method($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>|callable|array{ 0: class-string, 1: string }
     */
    public function callable_array_method_static($fb, $value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>|callable|array{ 0: object, 1: string }
     */
    public function callable_array_method_non_static($fb, $value, $newScope = 'static');

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function callable_string($fb, $value, $newScope = 'static');

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function callable_string_function($fb, $value);

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function callable_string_function_internal($fb, $value);

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function callable_string_function_non_internal($fb, $value);

    /**
     * @return Ret<callable|string>|callable|string
     */
    public function callable_string_method_static($fb, $value, $newScope = 'static');

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function path($fb, $value, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function path_normalized($fb, $value, ?string $separator = null, array $refs = []);

    /**
     * @return Ret<true>|true
     */
    public function is_os_windows($fb);

    /**
     * @return Ret<true>|true
     */
    public function is_sapi_terminal($fb);

    /**
     * @return Ret<string>|string
     */
    public function is_extension_loaded($fb, $extension);

    /**
     * @return Ret<int>|int
     */
    public function chmod($fb, $value);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function realpath($fb, $value, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function freepath($fb, $value, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function freepath_normalized($fb, $value, ?string $separator = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function dirpath($fb, $value, ?bool $isAllowExists, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function filepath($fb, $value, ?bool $isAllowExists, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function dirpath_realpath($fb, $value, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>|string
     */
    public function filepath_realpath($fb, $value, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @return Ret<string>|string
     */
    public function filename($fb, $value);

    /**
     * @return Ret<\SplFileInfo>|\SplFileInfo
     */
    public function file($fb, $value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null, array $refs = []);

    /**
     * @return Ret<\SplFileInfo>|\SplFileInfo
     */
    public function image($fb, $value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null, array $refs = []);

    /**
     * @return Ret<resource|\Socket>|resource|\Socket
     */
    public function socket($fb, $value);

    /**
     * @return Ret<resource>|resource
     */
    public function stream($fb, $value);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function email($fb, $value, ?array $filters = null, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function email_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function email_non_fake($fb, $value, ?array $filters = null, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function email_maybe_fake($fb, $value, ?array $filters = null, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function phone($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function phone_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function phone_non_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function phone_maybe_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string, 3?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function phone_real($fb, $value, ?string $region = '', array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function tel($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function tel_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function tel_non_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function tel_maybe_fake($fb, $value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>|string
     */
    public function tel_real($fb, $value, ?string $region = '', array $refs = []);
}
