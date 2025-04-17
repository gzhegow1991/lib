<?php

/**
 * This class is autogenerated.
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Arr\ArrPath;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Modules\Net\AddressIpV4;
use Gzhegow\Lib\Modules\Net\AddressIpV6;
use Gzhegow\Lib\Modules\Net\SubnetV4;
use Gzhegow\Lib\Modules\Net\SubnetV6;
use Gzhegow\Lib\Modules\Str\Alphabet;
use Gzhegow\Lib\Modules\Type\Base\AbstractParseModule;
use Gzhegow\Lib\Modules\Type\Number;

class ParseModule extends AbstractParseModule
{
	/**
	 * @return bool|null
	 */
	public function bool($value)
	{
		if (Lib::type()->bool($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return bool|null
	 */
	public function userbool($value)
	{
		if (Lib::type()->userbool($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|null
	 */
	public function int($value)
	{
		if (Lib::type()->int($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|null
	 */
	public function int_non_zero($value)
	{
		if (Lib::type()->int_non_zero($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|null
	 */
	public function int_non_negative($value)
	{
		if (Lib::type()->int_non_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|null
	 */
	public function int_non_positive($value)
	{
		if (Lib::type()->int_non_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|null
	 */
	public function int_negative($value)
	{
		if (Lib::type()->int_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|null
	 */
	public function int_positive($value)
	{
		if (Lib::type()->int_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return float|null
	 */
	public function float($value)
	{
		if (Lib::type()->float($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return float|null
	 */
	public function float_non_zero($value)
	{
		if (Lib::type()->float_non_zero($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return float|null
	 */
	public function float_non_negative($value)
	{
		if (Lib::type()->float_non_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return float|null
	 */
	public function float_non_positive($value)
	{
		if (Lib::type()->float_non_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return float|null
	 */
	public function float_negative($value)
	{
		if (Lib::type()->float_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return float|null
	 */
	public function float_positive($value)
	{
		if (Lib::type()->float_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|float|null
	 */
	public function num($value)
	{
		if (Lib::type()->num($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|float|null
	 */
	public function num_non_zero($value)
	{
		if (Lib::type()->num_non_zero($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|float|null
	 */
	public function num_non_negative($value)
	{
		if (Lib::type()->num_non_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|float|null
	 */
	public function num_non_positive($value)
	{
		if (Lib::type()->num_non_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|float|null
	 */
	public function num_negative($value)
	{
		if (Lib::type()->num_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return int|float|null
	 */
	public function num_positive($value)
	{
		if (Lib::type()->num_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric($value, ?bool $isAllowExp = null, array $refs = [])
	{
		if (Lib::type()->numeric($result, $value, $isAllowExp, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_non_zero($value, ?bool $allowExp = null, array $refs = [])
	{
		if (Lib::type()->numeric_non_zero($result, $value, $allowExp, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_non_negative($value, ?bool $allowExp = null, array $refs = [])
	{
		if (Lib::type()->numeric_non_negative($result, $value, $allowExp, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_non_positive($value, ?bool $allowExp = null, array $refs = [])
	{
		if (Lib::type()->numeric_non_positive($result, $value, $allowExp, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_negative($value, ?bool $allowExp = null, array $refs = [])
	{
		if (Lib::type()->numeric_negative($result, $value, $allowExp, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_positive($value, ?bool $allowExp = null, array $refs = [])
	{
		if (Lib::type()->numeric_positive($result, $value, $allowExp, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int($value, array $refs = [])
	{
		if (Lib::type()->numeric_int($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_non_zero($value, array $refs = [])
	{
		if (Lib::type()->numeric_int_non_zero($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_non_negative($value, array $refs = [])
	{
		if (Lib::type()->numeric_int_non_negative($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_non_positive($value, array $refs = [])
	{
		if (Lib::type()->numeric_int_non_positive($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_negative($value, array $refs = [])
	{
		if (Lib::type()->numeric_int_negative($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_positive($value, array $refs = [])
	{
		if (Lib::type()->numeric_int_positive($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return Number|null
	 */
	public function number($value, ?bool $allowExp = null)
	{
		if (Lib::type()->number($result, $value, $allowExp)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return Bcnumber|null
	 */
	public function bcnumber($value)
	{
		if (Lib::type()->bcnumber($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function string($value)
	{
		if (Lib::type()->string($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function string_not_empty($value)
	{
		if (Lib::type()->string_not_empty($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function trim($value, ?string $characters = null)
	{
		if (Lib::type()->trim($result, $value, $characters)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function char($value)
	{
		if (Lib::type()->char($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function letter($value)
	{
		if (Lib::type()->letter($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return Alphabet|null
	 */
	public function alphabet($value)
	{
		if (Lib::type()->alphabet($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function ctype_digit($value)
	{
		if (Lib::type()->ctype_digit($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function ctype_alpha($value, ?bool $isIgnoreCase = null)
	{
		if (Lib::type()->ctype_alpha($result, $value, $isIgnoreCase)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function ctype_alnum($value, ?bool $isIgnoreCase = null)
	{
		if (Lib::type()->ctype_alnum($result, $value, $isIgnoreCase)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function base($value, $alphabet)
	{
		if (Lib::type()->base($result, $value, $alphabet)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function base_bin($value)
	{
		if (Lib::type()->base_bin($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function base_oct($value)
	{
		if (Lib::type()->base_oct($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function base_dec($value)
	{
		if (Lib::type()->base_dec($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function base_hex($value)
	{
		if (Lib::type()->base_hex($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function list($value)
	{
		if (Lib::type()->list($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function list_sorted($value)
	{
		if (Lib::type()->list_sorted($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function dict($value)
	{
		if (Lib::type()->dict($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function dict_sorted($value)
	{
		if (Lib::type()->dict_sorted($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function index_list($value)
	{
		if (Lib::type()->index_list($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function index_dict($value)
	{
		if (Lib::type()->index_dict($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function table($value)
	{
		if (Lib::type()->table($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function matrix($value)
	{
		if (Lib::type()->matrix($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|null
	 */
	public function matrix_strict($value)
	{
		if (Lib::type()->matrix_strict($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return ArrPath|null
	 */
	public function arrpath($path, ?string $dot = null)
	{
		if (Lib::type()->arrpath($result, $path, $dot)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function regex($value)
	{
		if (Lib::type()->regex($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return AddressIpV4|AddressIpV6|null
	 */
	public function address_ip($value)
	{
		if (Lib::type()->address_ip($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return AddressIpV4|null
	 */
	public function address_ip_v4($value)
	{
		if (Lib::type()->address_ip_v4($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return AddressIpV6|null
	 */
	public function address_ip_v6($value)
	{
		if (Lib::type()->address_ip_v6($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function address_mac($value)
	{
		if (Lib::type()->address_mac($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return SubnetV4|SubnetV6|null
	 */
	public function subnet($value, ?string $ipFallback = null)
	{
		if (Lib::type()->subnet($result, $value, $ipFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return SubnetV4|null
	 */
	public function subnet_v4($value, ?string $ipFallback = null)
	{
		if (Lib::type()->subnet_v4($result, $value, $ipFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return SubnetV6|null
	 */
	public function subnet_v6($value, ?string $ipFallback = null)
	{
		if (Lib::type()->subnet_v6($result, $value, $ipFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function url($value, $query = null, $fragment = null, array $refs = [])
	{
		if (Lib::type()->url($result, $value, $query, $fragment, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function host($value, array $refs = [])
	{
		if (Lib::type()->host($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function link($value, $query = null, $fragment = null, array $refs = [])
	{
		if (Lib::type()->link($result, $value, $query, $fragment, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function uuid($value)
	{
		if (Lib::type()->uuid($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array|\Countable|null
	 */
	public function countable($value)
	{
		if (Lib::type()->countable($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \Countable|null
	 */
	public function countable_object($value)
	{
		if (Lib::type()->countable_object($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|array|\Countable|null
	 */
	public function sizeable($value)
	{
		if (Lib::type()->sizeable($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeZone|null
	 */
	public function timezone($value, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->timezone($result, $value, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeZone|null
	 */
	public function timezone_offset($timezoneOrOffset)
	{
		if (Lib::type()->timezone_offset($result, $timezoneOrOffset)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeZone|null
	 */
	public function timezone_abbr($timezoneOrAbbr)
	{
		if (Lib::type()->timezone_abbr($result, $timezoneOrAbbr)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeZone|null
	 */
	public function timezone_name($timezoneOrName)
	{
		if (Lib::type()->timezone_name($result, $timezoneOrName)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeZone|null
	 */
	public function timezone_nameabbr($timezoneOrNameOrAbbr)
	{
		if (Lib::type()->timezone_nameabbr($result, $timezoneOrNameOrAbbr)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeInterface|null
	 */
	public function date($datestring, $timezoneFallback = null)
	{
		if (Lib::type()->date($result, $datestring, $timezoneFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTime|null
	 */
	public function adate($datestring, $timezoneFallback = null)
	{
		if (Lib::type()->adate($result, $datestring, $timezoneFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeImmutable|null
	 */
	public function idate($datestring, $timezoneFallback = null)
	{
		if (Lib::type()->idate($result, $datestring, $timezoneFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeInterface|null
	 */
	public function date_formatted($formats, $dateFormatted, $timezoneFallback = null)
	{
		if (Lib::type()->date_formatted($result, $formats, $dateFormatted, $timezoneFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTime|null
	 */
	public function adate_formatted($formats, $dateFormatted, $timezoneFallback = null)
	{
		if (Lib::type()->adate_formatted($result, $formats, $dateFormatted, $timezoneFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeImmutable|null
	 */
	public function idate_formatted($formats, $dateFormatted, $timezoneFallback = null)
	{
		if (Lib::type()->idate_formatted($result, $formats, $dateFormatted, $timezoneFallback)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeInterface|null
	 */
	public function date_tz($datestring, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->date_tz($result, $datestring, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTime|null
	 */
	public function adate_tz($datestring, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->adate_tz($result, $datestring, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeImmutable|null
	 */
	public function idate_tz($datestring, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->idate_tz($result, $datestring, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeInterface|null
	 */
	public function date_tz_formatted($formats, $dateFormatted, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->date_tz_formatted($result, $formats, $dateFormatted, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTime|null
	 */
	public function adate_tz_formatted($formats, $dateFormatted, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->adate_tz_formatted($result, $formats, $dateFormatted, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeImmutable|null
	 */
	public function idate_tz_formatted($formats, $dateFormatted, ?array $allowedTimezoneTypes = null)
	{
		if (Lib::type()->idate_tz_formatted($result, $formats, $dateFormatted, $allowedTimezoneTypes)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeInterface|null
	 */
	public function date_microtime($microtime, $timezoneSet = null)
	{
		if (Lib::type()->date_microtime($result, $microtime, $timezoneSet)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTime|null
	 */
	public function adate_microtime($microtime, $timezoneSet = null)
	{
		if (Lib::type()->adate_microtime($result, $microtime, $timezoneSet)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateTimeImmutable|null
	 */
	public function idate_microtime($microtime, $timezoneSet = null)
	{
		if (Lib::type()->idate_microtime($result, $microtime, $timezoneSet)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateInterval|null
	 */
	public function interval($interval)
	{
		if (Lib::type()->interval($result, $interval)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateInterval|null
	 */
	public function interval_duration($duration)
	{
		if (Lib::type()->interval_duration($result, $duration)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateInterval|null
	 */
	public function interval_datestring($datestring)
	{
		if (Lib::type()->interval_datestring($result, $datestring)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateInterval|null
	 */
	public function interval_microtime($microtime)
	{
		if (Lib::type()->interval_microtime($result, $microtime)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \DateInterval|null
	 */
	public function interval_ago($date, ?\DateTimeInterface $from = null, ?bool $reverse = null)
	{
		if (Lib::type()->interval_ago($result, $date, $from, $reverse)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return class-string<T>|null
	 */
	public function struct_exists($value, ?int $flags = null)
	{
		if (Lib::type()->struct_exists($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return class-string<T>|null
	 */
	public function struct($value, ?int $flags = null)
	{
		if (Lib::type()->struct($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return class-string<T>|null
	 */
	public function struct_class($value, ?int $flags = null)
	{
		if (Lib::type()->struct_class($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return class-string|null
	 */
	public function struct_interface($value, ?int $flags = null)
	{
		if (Lib::type()->struct_interface($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return class-string|null
	 */
	public function struct_trait($value, ?int $flags = null)
	{
		if (Lib::type()->struct_trait($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @template-covariant T of \UnitEnum
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return class-string<T>|null
	 */
	public function struct_enum($value, ?int $flags = null)
	{
		if (Lib::type()->struct_enum($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return class-string<T>|null
	 */
	public function struct_fqcn($value, ?int $flags = null)
	{
		if (Lib::type()->struct_fqcn($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function struct_namespace($value, ?int $flags = null)
	{
		if (Lib::type()->struct_namespace($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function struct_basename($value, ?int $flags = null)
	{
		if (Lib::type()->struct_basename($result, $value, $flags)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return resource|null
	 */
	public function resource($value)
	{
		if (Lib::type()->resource($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return resource|null
	 */
	public function resource_opened($value)
	{
		if (Lib::type()->resource_opened($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return resource|null
	 */
	public function resource_closed($value)
	{
		if (Lib::type()->resource_closed($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @template-covariant T of \UnitEnum
	 *
	 * @param T|int|string         $value
	 * @param class-string<T>|null $enumClass
	 *
	 * @return T|null
	 */
	public function enum_case($value, ?string $enumClass = null)
	{
		if (Lib::type()->enum_case($result, $value, $enumClass)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return array{
	 */
	public function method_array($value)
	{
		if (Lib::type()->method_array($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return string|null
	 */
	public function method_string($value, array $refs = [])
	{
		if (Lib::type()->method_string($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param string|object $newScope
	 *
	 * @return callable|null
	 */
	public function callable($value, $newScope = 'static')
	{
		if (Lib::type()->callable($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable|\Closure|object|null
	 */
	public function callable_object($value, $newScope = 'static')
	{
		if (Lib::type()->callable_object($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable|object|null
	 */
	public function callable_object_closure($value, $newScope = 'static')
	{
		if (Lib::type()->callable_object_closure($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable|object|null
	 */
	public function callable_object_invokable($value, $newScope = 'static')
	{
		if (Lib::type()->callable_object_invokable($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param string|object                                            $newScope
	 *
	 * @return callable|array{
	 */
	public function callable_array($value, $newScope = 'static')
	{
		if (Lib::type()->callable_array($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param string|object                                            $newScope
	 *
	 * @return callable|array{
	 */
	public function callable_array_method($value, $newScope = 'static')
	{
		if (Lib::type()->callable_array_method($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param string|object                                     $newScope
	 *
	 * @return callable|array{
	 */
	public function callable_array_method_static($value, $newScope = 'static')
	{
		if (Lib::type()->callable_array_method_static($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param string|object                               $newScope
	 *
	 * @return callable|array{
	 */
	public function callable_array_method_non_static($value, $newScope = 'static')
	{
		if (Lib::type()->callable_array_method_non_static($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable-string|null
	 */
	public function callable_string($value, $newScope = 'static')
	{
		if (Lib::type()->callable_string($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable-string|null
	 */
	public function callable_string_function($value)
	{
		if (Lib::type()->callable_string_function($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable-string|null
	 */
	public function callable_string_function_internal($value)
	{
		if (Lib::type()->callable_string_function_internal($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable-string|null
	 */
	public function callable_string_function_non_internal($value)
	{
		if (Lib::type()->callable_string_function_non_internal($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return callable-string|null
	 */
	public function callable_string_method_static($value, $newScope = 'static')
	{
		if (Lib::type()->callable_string_method_static($result, $value, $newScope)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return string|null
	 */
	public function path($value, array $refs = [])
	{
		if (Lib::type()->path($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return string|null
	 */
	public function realpath($value, ?bool $allowSymlink = null, array $refs = [])
	{
		if (Lib::type()->realpath($result, $value, $allowSymlink, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return string|null
	 */
	public function dirpath($value, ?bool $allowExists = null, ?bool $allowSymlink = null, array $refs = [])
	{
		if (Lib::type()->dirpath($result, $value, $allowExists, $allowSymlink, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function filepath($value, ?bool $allowExists = null, ?bool $allowSymlink = null, array $refs = [])
	{
		if (Lib::type()->filepath($result, $value, $allowExists, $allowSymlink, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return string|null
	 */
	public function dirpath_realpath($value, ?bool $allowSymlink = null, array $refs = [])
	{
		if (Lib::type()->dirpath_realpath($result, $value, $allowSymlink, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return string|null
	 */
	public function filepath_realpath($value, ?bool $allowSymlink = null, array $refs = [])
	{
		if (Lib::type()->filepath_realpath($result, $value, $allowSymlink, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function filename($value)
	{
		if (Lib::type()->filename($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \SplFileInfo|null
	 */
	public function file($value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null)
	{
		if (Lib::type()->file($result, $value, $extensions, $mimeTypes, $filters)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return \SplFileInfo|null
	 */
	public function image($value, ?array $extensions = null, ?array $mimeTypes = null, ?array $filters = null)
	{
		if (Lib::type()->image($result, $value, $extensions, $mimeTypes, $filters)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function email($value, ?array $filters = null, array $refs = [])
	{
		if (Lib::type()->email($result, $value, $filters, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function email_fake($value, array $refs = [])
	{
		if (Lib::type()->email_fake($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function email_non_fake($value, ?array $filters = null, array $refs = [])
	{
		if (Lib::type()->email_non_fake($result, $value, $filters, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function phone($value, array $refs = [])
	{
		if (Lib::type()->phone($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function phone_fake($value, array $refs = [])
	{
		if (Lib::type()->phone_fake($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function phone_non_fake($value, array $refs = [])
	{
		if (Lib::type()->phone_non_fake($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function phone_real($value, array $refs = [])
	{
		if (Lib::type()->phone_real($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function tel($value, array $refs = [])
	{
		if (Lib::type()->tel($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function tel_fake($value, array $refs = [])
	{
		if (Lib::type()->tel_fake($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function tel_non_fake($value, array $refs = [])
	{
		if (Lib::type()->tel_non_fake($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function tel_real($value, array $refs = [])
	{
		if (Lib::type()->tel_real($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}
}
