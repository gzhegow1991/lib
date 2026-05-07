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


interface TypeInterface
{
    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     *
     * @return Ret<string|array|\Countable|null>
     */
    public function blank($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_blank($value);

    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     *
     * @return Ret<mixed>
     */
    public function nullable($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_nullable($value);

    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из PHP
     *
     * @return Ret<mixed>
     */
    public function client($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_client($value);

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
    public function nil($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_nil($value);

    /**
     * @return Ret<null>
     */
    public function null($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_null($value);

    /**
     * @return Ret<bool>
     */
    public function php_bool($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_php_bool($value);

    /**
     * @return Ret<false>
     */
    public function php_bool_false($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_php_bool_false($value);

    /**
     * @return Ret<true>
     */
    public function php_bool_true($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_php_bool_true($value);

    /**
     * @return Ret<bool>
     */
    public function bool($value);

    /**
     * @return Ret<false>
     */
    public function bool_false($value);

    /**
     * @return Ret<true>
     */
    public function bool_true($value);

    /**
     * @return Ret<bool>
     */
    public function userbool($value);

    /**
     * @return Ret<false>
     */
    public function userbool_false($value);

    /**
     * @return Ret<false>
     */
    public function userbool_true($value);

    /**
     * @return Ret<array>
     */
    public function array($value);

    /**
     * @return Ret<array>
     */
    public function array_empty($value);

    /**
     * @return Ret<array>
     */
    public function array_not_empty($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_array($value);

    /**
     * @return Ret<object>
     */
    public function object($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_object($value);

    /**
     * @return Ret<\stdClass>
     */
    public function stdclass($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_stdclass($value);

    /**
     * @return Ret<float>
     */
    public function php_int($value);

    /**
     * @return Ret<float>
     */
    public function php_float($value);

    /**
     * @return Ret<float>
     */
    public function nan($value);

    /**
     * @return Ret<float>
     */
    public function float_not_nan($value);

    /**
     * @return Ret<float>
     */
    public function float_maybe_nan($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_nan($value);

    /**
     * @return Ret<float>
     */
    public function finite($value);

    /**
     * @return Ret<float>
     */
    public function float_not_finite($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_finite($value);

    /**
     * @return Ret<float>
     */
    public function infinite($value);

    /**
     * @return Ret<float>
     */
    public function float_not_infinite($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_infinite($value);

    /**
     * @return Ret<float>
     */
    public function float_min($value);

    /**
     * @return Ret<float>
     */
    public function float_not_float_min($value);

    /**
     * @return Ret<mixed>
     */
    public function any_not_float_min($value);

    /**
     * @return Ret<string>
     */
    public function numeric($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_int($value, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_float($value, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function decimal($value, int $scale = 0, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_non_zero($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_non_negative($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_non_positive($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_negative($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_positive($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_non_negative_or_minus_one($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_positive_or_minus_one($value, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_gt($value, $gt, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_gte($value, $gte, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_lt($value, $lt, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_lte($value, $lte, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_between($value, $from, $to, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function numeric_inside($value, $from, $to, ?bool $isAllowExp = null, array $refs = []);

    /**
     * @return Ret<int|float>
     */
    public function num($value);

    /**
     * @return Ret<int>
     */
    public function int($value);

    /**
     * @return Ret<float>
     */
    public function float($value);

    /**
     * @return Ret<int|float>
     */
    public function num_non_zero($value);

    /**
     * @return Ret<int|float>
     */
    public function num_non_negative($value);

    /**
     * @return Ret<int|float>
     */
    public function num_non_positive($value);

    /**
     * @return Ret<int|float>
     */
    public function num_negative($value);

    /**
     * @return Ret<int|float>
     */
    public function num_positive($value);

    /**
     * @return Ret<int|float>
     */
    public function num_non_negative_or_minus_one($value);

    /**
     * @return Ret<int|float>
     */
    public function num_positive_or_minus_one($value);

    /**
     * @return Ret<int|float>
     */
    public function num_gt($value, $gt);

    /**
     * @return Ret<int|float>
     */
    public function num_gte($value, $gte);

    /**
     * @return Ret<int|float>
     */
    public function num_lt($value, $lt);

    /**
     * @return Ret<int|float>
     */
    public function num_lte($value, $lte);

    /**
     * @return Ret<int|float>
     */
    public function num_between($value, $from, $to);

    /**
     * @return Ret<int|float>
     */
    public function num_inside($value, $from, $to);

    /**
     * @return Ret<int>
     */
    public function int_non_zero($value);

    /**
     * @return Ret<int>
     */
    public function int_non_negative($value);

    /**
     * @return Ret<int>
     */
    public function int_non_positive($value);

    /**
     * @return Ret<int>
     */
    public function int_negative($value);

    /**
     * @return Ret<int>
     */
    public function int_positive($value);

    /**
     * @return Ret<int>
     */
    public function int_non_negative_or_minus_one($value);

    /**
     * @return Ret<int>
     */
    public function int_positive_or_minus_one($value);

    /**
     * @return Ret<int>
     */
    public function int_gt($value, $gt);

    /**
     * @return Ret<int>
     */
    public function int_gte($value, $gte);

    /**
     * @return Ret<int>
     */
    public function int_lt($value, $lt);

    /**
     * @return Ret<int>
     */
    public function int_lte($value, $lte);

    /**
     * @return Ret<int>
     */
    public function int_between($value, $from, $to);

    /**
     * @return Ret<int>
     */
    public function int_inside($value, $from, $to);

    /**
     * @return Ret<float>
     */
    public function float_non_zero($value);

    /**
     * @return Ret<float>
     */
    public function float_non_negative($value);

    /**
     * @return Ret<float>
     */
    public function float_non_positive($value);

    /**
     * @return Ret<float>
     */
    public function float_negative($value);

    /**
     * @return Ret<float>
     */
    public function float_positive($value);

    /**
     * @return Ret<float>
     */
    public function float_non_negative_or_minus_one($value);

    /**
     * @return Ret<float>
     */
    public function float_positive_or_minus_one($value);

    /**
     * @return Ret<float>
     */
    public function float_gt($value, $gt);

    /**
     * @return Ret<float>
     */
    public function float_gte($value, $gte);

    /**
     * @return Ret<float>
     */
    public function float_lt($value, $lt);

    /**
     * @return Ret<float>
     */
    public function float_lte($value, $lte);

    /**
     * @return Ret<float>
     */
    public function float_between($value, $from, $to);

    /**
     * @return Ret<float>
     */
    public function float_inside($value, $from, $to);

    /**
     * @return Ret<Number>
     */
    public function number($value, ?bool $isAllowExp = null);

    /**
     * @return Ret<int>|int
     */
    public function exponent($value);

    /**
     * @return Ret<int>
     */
    public function scale($value);

    /**
     * @return Ret<int>
     */
    public function percent($value);

    /**
     * @return Ret<string>
     */
    public function percent_numeric($value);

    /**
     * @return Ret<float>
     */
    public function ratio($value);

    /**
     * @return Ret<string>
     */
    public function ratio_numeric($value);

    /**
     * @return Ret<Bcnumber>
     */
    public function bcnumber($value);

    /**
     * @return Ret<string>
     */
    public function php_string($value);

    /**
     * @return Ret<string>
     */
    public function php_string_empty($value);

    /**
     * @return Ret<string>
     */
    public function php_string_not_empty($value);

    /**
     * @return Ret<string>
     */
    public function php_trim($value);

    /**
     * @return Ret<string>
     */
    public function string($value);

    /**
     * @return Ret<string>
     */
    public function string_empty($value);

    /**
     * @return Ret<string>
     */
    public function string_not_empty($value);

    /**
     * @return Ret<string>
     */
    public function trim($value, ?string $characters = null);

    /**
     * @return Ret<string>
     */
    public function char($value);

    /**
     * @return Ret<string>
     */
    public function letter($value);

    /**
     * @return Ret<string>
     */
    public function word($value);

    /**
     * @return Ret<Alphabet>
     */
    public function alphabet($value);

    /**
     * @return Ret<string>
     */
    public function ctype_digit($value);

    /**
     * @return Ret<string>
     */
    public function ctype_alpha($value, ?bool $allowUpperCase = null);

    /**
     * @return Ret<string>
     */
    public function ctype_alnum($value, ?bool $allowUpperCase = null);

    /**
     * @return Ret<string>
     */
    public function base($value, $alphabet);

    /**
     * @return Ret<string>
     */
    public function base_bin($value);

    /**
     * @return Ret<string>
     */
    public function base_oct($value);

    /**
     * @return Ret<string>
     */
    public function base_dec($value);

    /**
     * @return Ret<string>
     */
    public function base_hex($value);

    /**
     * @return Ret<int|string>
     */
    public function key($key);

    /**
     * @return Ret<mixed>
     */
    public function key_exists(array $array, $key);

    /**
     * @return Ret<mixed>
     */
    public function value_in_array(array $array, $key, ?bool $strict = null);

    /**
     * @return Ret<int|string>
     */
    public function value_in_array_key(array $array, $key, ?bool $strict = null);

    /**
     * @return Ret<int>
     */
    public function value_in_array_pos(array $array, $key, ?bool $strict = null);

    /**
     * @return Ret<array>
     */
    public function keys_exists(array $array, $keys);

    /**
     * @return Ret<array>
     */
    public function keys_not_exists(array $array, $keys);

    /**
     * @return Ret<array>
     */
    public function values_in_array(array $array, $keys, ?bool $strict = null);

    /**
     * @return Ret<array>
     */
    public function values_not_in_array(array $array, $keys, ?bool $strict = null);

    /**
     * @return Ret<array>
     */
    public function array_plain($value, ?int $maxDepth = null);

    /**
     * @return Ret<array>
     */
    public function list($value, ?int $plainMaxDepth = null);

    /**
     * @return Ret<array>
     */
    public function list_sorted($value, ?int $plainMaxDepth = null);

    /**
     * @return Ret<array>
     */
    public function dict($value, ?int $plainMaxDepth = null);

    /**
     * @return Ret<array>
     */
    public function dict_sorted($value, ?int $plainMaxDepth = null, $fnSortCmp = null);

    /**
     * @return Ret<array>
     */
    public function table($value);

    /**
     * @return Ret<array>
     */
    public function matrix($value);

    /**
     * @return Ret<array>
     */
    public function matrix_sorted($value);

    /**
     * @return Ret<ArrPath>
     */
    public function arrpath($path);

    /**
     * @return Ret<ArrPath>
     */
    public function arrpath_dot($path, ?string $dot = '.');

    /**
     * @return Ret<array>
     */
    public function array_of_type($value, string $type);

    /**
     * @return Ret<resource[]>
     */
    public function array_of_resource_type($value, string $resourceType);

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>
     */
    public function array_of_a($value, string $className);

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>
     */
    public function array_of_class($value, string $className);

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return Ret<T[]>
     */
    public function array_of_subclass($value, string $className);

    /**
     * @param callable $fn
     *
     * @return Ret<array>
     *
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    public function array_of_callback($value, callable $fn, array $args = []);

    /**
     * @return Ret<array{ 0: string, 1: int }>
     */
    public function file_line($value);

    /**
     * @return Ret<string>
     */
    public function html_tag($value);

    /**
     * @return Ret<string>
     */
    public function xml_tag($value);

    /**
     * @return Ret<string>
     */
    public function xml_nstag($value);

    /**
     * @return Ret<string>
     */
    public function regex($value);

    /**
     * @return Ret<string>
     */
    public function regexp($value);

    /**
     * @return Ret<AddressIpV4|AddressIpV6>
     */
    public function address_ip($value);

    /**
     * @return Ret<AddressIpV4>
     */
    public function address_ip_v4($value);

    /**
     * @return Ret<AddressIpV6>
     */
    public function address_ip_v6($value);

    /**
     * @return Ret<string>
     */
    public function address_mac($value);

    /**
     * @return Ret<SubnetV4|SubnetV6>
     */
    public function subnet($value, ?string $ipFallback = null);

    /**
     * @return Ret<SubnetV4>
     */
    public function subnet_v4($value, ?string $ipFallback = null);

    /**
     * @return Ret<SubnetV6>
     */
    public function subnet_v6($value, ?string $ipFallback = null);

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function url($value, $query = null, $fragment = null, ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null, array $refs = []);

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function uri($value, $query = null, $fragment = null, ?int $isHostIdnaAscii = null, ?int $isLinkUrlencoded = null, array $refs = []);

    /**
     * @param string|true $value
     *
     * @return Ret<string>
     */
    public function host($value, ?int $isHostIdnaAscii = null, array $refs = []);

    /**
     * @param string|true $value
     *
     * @return Ret<string>
     */
    public function domain($value, ?int $isHostIdnaAscii = null, array $refs = []);

    /**
     * @param string|true             $value
     * @param string|false|array|null $query
     * @param string|false|null       $fragment
     *
     * @return Ret<string>
     */
    public function link($value, $query = null, $fragment = null, ?int $isLinkUrlencoded = null, array $refs = []);

    /**
     * @param string $value
     *
     * @return Ret<string>
     */
    public function dsn_pdo($value, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function uuid($value);

    /**
     * @return Ret<array|\Countable>
     */
    public function countable($value);

    /**
     * @return Ret<\Countable>
     */
    public function countable_object($value);

    /**
     * @return Ret<string|array|\Countable>
     */
    public function sizeable($value);

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone($timezone, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_offset($timezoneOrOffset);

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_abbr($timezoneOrAbbr);

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_name($timezoneOrName);

    /**
     * @return Ret<\DateTimeZone>
     */
    public function timezone_nameabbr($timezoneOrNameOrAbbr);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date($datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate($datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate($datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_formatted($dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate_formatted($dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_formatted($dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_tz($datestring, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate_tz($datestring, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_tz($datestring, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_tz_formatted($dateFormatted, $formats, ?array $allowedTimezoneTypes = null);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_no_tz($datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate_no_tz($datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_no_tz($datestring, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_no_tz_formatted($dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate_no_tz_formatted($dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_no_tz_formatted($dateFormatted, $formats, $timezoneFallback = null);

    /**
     * @return Ret<\DateTimeInterface>
     */
    public function date_microtime($microtime, $timezoneSet = null);

    /**
     * @return Ret<\DateTime>
     */
    public function adate_microtime($microtime, $timezoneSet = null);

    /**
     * @return Ret<\DateTimeImmutable>
     */
    public function idate_microtime($microtime, $timezoneSet = null);

    /**
     * @return Ret<\DateInterval>
     */
    public function interval($interval);

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_duration($duration);

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_datestring($datestring);

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_microtime($microtime);

    /**
     * @return Ret<\DateInterval>
     */
    public function interval_ago($date, ?\DateTimeInterface $from = null, ?bool $reverse = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_exists($value, ?int $flags = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct($value, ?int $flags = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_class($value, ?int $flags = null);

    /**
     * @return Ret<class-string>
     */
    public function struct_interface($value, ?int $flags = null);

    /**
     * @return Ret<class-string>
     */
    public function struct_trait($value, ?int $flags = null);

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_enum($value, ?int $flags = null);

    /**
     * @template-covariant T of object
     *
     * @param class-string<T>|T|mixed $value
     *
     * @return Ret<class-string<T>>|class-string<T>
     */
    public function struct_fqcn($value, ?int $flags = null);

    /**
     * @return Ret<string>
     */
    public function struct_namespace($value, ?int $flags = null);

    /**
     * @return Ret<string>
     */
    public function struct_basename($value, ?int $flags = null);

    /**
     * @return Ret<resource>
     */
    public function resource($value, ?string $resourceType = null);

    /**
     * @return Ret<resource>
     */
    public function resource_opened($value, ?string $resourceType = null);

    /**
     * @return Ret<resource>
     */
    public function resource_closed($value);

    /**
     * @return Ret<resource>
     */
    public function any_not_resource($value);

    /**
     * @return Ret<resource|\CurlHandle>
     */
    public function curl($value);

    /**
     * @template-covariant T of \UnitEnum
     *
     * @param T|int|string         $value
     * @param class-string<T>|null $enumClass
     *
     * @return Ret<T>|T
     */
    public function enum_case($value, ?string $enumClass = null);

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
    public function method($value, array $refs = []);

    /**
     * @return Ret<array{ 0: class-string, 1: string }>
     */
    public function method_array($value);

    /**
     * @return Ret<string>
     */
    public function method_string($value);

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable>
     */
    public function callable($value, $newScope = 'static');

    /**
     * @return Ret<callable|\Closure|object>
     */
    public function callable_object($value, $newScope = 'static');

    /**
     * @return Ret<\Closure>
     */
    public function callable_object_closure($value, $newScope = 'static');

    /**
     * @return Ret<callable|object>
     */
    public function callable_object_invokable($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function callable_array($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object|class-string, 1: string }>
     */
    public function callable_array_method($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: class-string, 1: string }>
     */
    public function callable_array_method_static($value, $newScope = 'static');

    /**
     * @param string|object $newScope
     *
     * @return Ret<callable|array{ 0: object, 1: string }>
     */
    public function callable_array_method_non_static($value, $newScope = 'static');

    /**
     * @return Ret<callable|string>
     */
    public function callable_string($value, $newScope = 'static');

    /**
     * @return Ret<callable|string>
     */
    public function callable_string_function($value);

    /**
     * @return Ret<callable|string>
     */
    public function callable_string_function_internal($value);

    /**
     * @return Ret<callable|string>
     */
    public function callable_string_function_non_internal($value);

    /**
     * @return Ret<callable|string>
     */
    public function callable_string_method_static($value, $newScope = 'static');

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function path($value, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function path_normalized($value, ?string $separator = null, array $refs = []);

    /**
     * @return Ret<true>
     */
    public function is_os_windows();

    /**
     * @return Ret<true>
     */
    public function is_sapi_terminal();

    /**
     * @return Ret<string>
     */
    public function is_extension_loaded($extension);

    /**
     * @return Ret<int>
     */
    public function chmod($value);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function realpath($value, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function freepath($value, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function freepath_normalized($value, ?string $separator = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function dirpath($value, ?bool $isAllowExists, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function filepath($value, ?bool $isAllowExists, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function dirpath_realpath($value, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function filepath_realpath($value, ?bool $isAllowSymlink = null, array $refs = []);

    /**
     * @return Ret<string>
     */
    public function filename($value);

    /**
     * @return Ret<\SplFileInfo>
     */
    public function file($value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null, array $refs = []);

    /**
     * @return Ret<\SplFileInfo>
     */
    public function image($value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null, array $refs = []);

    /**
     * @return Ret<resource|\Socket>
     */
    public function socket($value);

    /**
     * @return Ret<resource>
     */
    public function stream($value);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email($value, ?array $filters = null, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email_non_fake($value, ?array $filters = null, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function email_maybe_fake($value, ?array $filters = null, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_non_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_maybe_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string, 3?: string } $refs
     *
     * @return Ret<string>
     */
    public function phone_real($value, ?string $region = '', array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_non_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_maybe_fake($value, array $refs = []);

    /**
     * @param array{ 0?: string, 1?: string, 2?: string } $refs
     *
     * @return Ret<string>
     */
    public function tel_real($value, ?string $region = '', array $refs = []);
}
