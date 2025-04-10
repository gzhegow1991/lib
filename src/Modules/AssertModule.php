<?php

/**
 * This class is autogenerated.
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Base\AssertModuleBase;

class AssertModule extends AssertModuleBase
{
	/**
	 * @return static
	 */
	public function bool($value)
	{
		$this->status = Lib::type()->bool($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function userbool($value)
	{
		$this->status = Lib::type()->userbool($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function int($value)
	{
		$this->status = Lib::type()->int($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function int_non_zero($value)
	{
		$this->status = Lib::type()->int_non_zero($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function int_non_negative($value)
	{
		$this->status = Lib::type()->int_non_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function int_non_positive($value)
	{
		$this->status = Lib::type()->int_non_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function int_negative($value)
	{
		$this->status = Lib::type()->int_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function int_positive($value)
	{
		$this->status = Lib::type()->int_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function float($value)
	{
		$this->status = Lib::type()->float($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function float_non_zero($value)
	{
		$this->status = Lib::type()->float_non_zero($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function float_non_negative($value)
	{
		$this->status = Lib::type()->float_non_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function float_non_positive($value)
	{
		$this->status = Lib::type()->float_non_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function float_negative($value)
	{
		$this->status = Lib::type()->float_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function float_positive($value)
	{
		$this->status = Lib::type()->float_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function num($value)
	{
		$this->status = Lib::type()->num($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function num_non_zero($value)
	{
		$this->status = Lib::type()->num_non_zero($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function num_non_negative($value)
	{
		$this->status = Lib::type()->num_non_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function num_non_positive($value)
	{
		$this->status = Lib::type()->num_non_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function num_negative($value)
	{
		$this->status = Lib::type()->num_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function num_positive($value)
	{
		$this->status = Lib::type()->num_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric($value, bool $isAllowExp = null, array $refs = [])
	{
		$this->status = Lib::type()->numeric($this->result, $value, $isAllowExp, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_non_zero($value, bool $allowExp = null, array $refs = [])
	{
		$this->status = Lib::type()->numeric_non_zero($this->result, $value, $allowExp, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_non_negative($value, bool $allowExp = null, array $refs = [])
	{
		$this->status = Lib::type()->numeric_non_negative($this->result, $value, $allowExp, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_non_positive($value, bool $allowExp = null, array $refs = [])
	{
		$this->status = Lib::type()->numeric_non_positive($this->result, $value, $allowExp, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_negative($value, bool $allowExp = null, array $refs = [])
	{
		$this->status = Lib::type()->numeric_negative($this->result, $value, $allowExp, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_positive($value, bool $allowExp = null, array $refs = [])
	{
		$this->status = Lib::type()->numeric_positive($this->result, $value, $allowExp, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int($value, array $refs = [])
	{
		$this->status = Lib::type()->numeric_int($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_non_zero($value, array $refs = [])
	{
		$this->status = Lib::type()->numeric_int_non_zero($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_non_negative($value, array $refs = [])
	{
		$this->status = Lib::type()->numeric_int_non_negative($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_non_positive($value, array $refs = [])
	{
		$this->status = Lib::type()->numeric_int_non_positive($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_negative($value, array $refs = [])
	{
		$this->status = Lib::type()->numeric_int_negative($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_positive($value, array $refs = [])
	{
		$this->status = Lib::type()->numeric_int_positive($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function number($value, bool $allowExp = null)
	{
		$this->status = Lib::type()->number($this->result, $value, $allowExp);

		return $this;
	}


	/**
	 * @return static
	 */
	public function bcnumber($value)
	{
		$this->status = Lib::type()->bcnumber($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function string($value)
	{
		$this->status = Lib::type()->string($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function string_not_empty($value)
	{
		$this->status = Lib::type()->string_not_empty($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function trim($value, string $characters = null)
	{
		$this->status = Lib::type()->trim($this->result, $value, $characters);

		return $this;
	}


	/**
	 * @return static
	 */
	public function char($value)
	{
		$this->status = Lib::type()->char($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function letter($value)
	{
		$this->status = Lib::type()->letter($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function alphabet($value)
	{
		$this->status = Lib::type()->alphabet($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function ctype_digit($value)
	{
		$this->status = Lib::type()->ctype_digit($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function ctype_alpha($value, bool $isIgnoreCase = null)
	{
		$this->status = Lib::type()->ctype_alpha($this->result, $value, $isIgnoreCase);

		return $this;
	}


	/**
	 * @return static
	 */
	public function ctype_alnum($value, bool $isIgnoreCase = null)
	{
		$this->status = Lib::type()->ctype_alnum($this->result, $value, $isIgnoreCase);

		return $this;
	}


	/**
	 * @return static
	 */
	public function base($value, $alphabet)
	{
		$this->status = Lib::type()->base($this->result, $value, $alphabet);

		return $this;
	}


	/**
	 * @return static
	 */
	public function base_bin($value)
	{
		$this->status = Lib::type()->base_bin($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function base_oct($value)
	{
		$this->status = Lib::type()->base_oct($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function base_dec($value)
	{
		$this->status = Lib::type()->base_dec($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function base_hex($value)
	{
		$this->status = Lib::type()->base_hex($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function list($value)
	{
		$this->status = Lib::type()->list($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function list_sorted($value)
	{
		$this->status = Lib::type()->list_sorted($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function dict($value)
	{
		$this->status = Lib::type()->dict($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function dict_sorted($value)
	{
		$this->status = Lib::type()->dict_sorted($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function index_list($value)
	{
		$this->status = Lib::type()->index_list($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function index_dict($value)
	{
		$this->status = Lib::type()->index_dict($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function table($value)
	{
		$this->status = Lib::type()->table($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function matrix($value)
	{
		$this->status = Lib::type()->matrix($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function matrix_strict($value)
	{
		$this->status = Lib::type()->matrix_strict($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function arrpath($path, array $pathes = null, string $dot = null)
	{
		$this->status = Lib::type()->arrpath($this->result, $path, $pathes, $dot);

		return $this;
	}


	/**
	 * @return static
	 */
	public function regex($value)
	{
		$this->status = Lib::type()->regex($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function address_ip($value)
	{
		$this->status = Lib::type()->address_ip($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function address_ip_v4($value)
	{
		$this->status = Lib::type()->address_ip_v4($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function address_ip_v6($value)
	{
		$this->status = Lib::type()->address_ip_v6($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function address_mac($value)
	{
		$this->status = Lib::type()->address_mac($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function subnet($value, string $ipFallback = null)
	{
		$this->status = Lib::type()->subnet($this->result, $value, $ipFallback);

		return $this;
	}


	/**
	 * @return static
	 */
	public function subnet_v4($value, string $ipFallback = null)
	{
		$this->status = Lib::type()->subnet_v4($this->result, $value, $ipFallback);

		return $this;
	}


	/**
	 * @return static
	 */
	public function subnet_v6($value, string $ipFallback = null)
	{
		$this->status = Lib::type()->subnet_v6($this->result, $value, $ipFallback);

		return $this;
	}


	/**
	 * @param string            $value
	 * @param string|array|null $query
	 * @param string|null       $fragment
	 *
	 * @return static
	 */
	public function url($value, $query = null, $fragment = null, array $refs = [])
	{
		$this->status = Lib::type()->url($this->result, $value, $query, $fragment, $refs);

		return $this;
	}


	/**
	 * @param string      $value
	 *
	 * @return static
	 */
	public function host($value, array $refs = [])
	{
		$this->status = Lib::type()->host($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @param string            $value
	 * @param string|array|null $query
	 * @param string|null       $fragment
	 *
	 * @return static
	 */
	public function link($value, $query = null, $fragment = null, array $refs = [])
	{
		$this->status = Lib::type()->link($this->result, $value, $query, $fragment, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function uuid($value)
	{
		$this->status = Lib::type()->uuid($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function countable($value)
	{
		$this->status = Lib::type()->countable($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function countable_object($value)
	{
		$this->status = Lib::type()->countable_object($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function sizeable($value)
	{
		$this->status = Lib::type()->sizeable($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function timezone($value, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->timezone($this->result, $value, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function timezone_offset($timezoneOrOffset)
	{
		$this->status = Lib::type()->timezone_offset($this->result, $timezoneOrOffset);

		return $this;
	}


	/**
	 * @return static
	 */
	public function timezone_abbr($timezoneOrAbbr)
	{
		$this->status = Lib::type()->timezone_abbr($this->result, $timezoneOrAbbr);

		return $this;
	}


	/**
	 * @return static
	 */
	public function timezone_name($timezoneOrName)
	{
		$this->status = Lib::type()->timezone_name($this->result, $timezoneOrName);

		return $this;
	}


	/**
	 * @return static
	 */
	public function timezone_nameabbr($timezoneOrNameOrAbbr)
	{
		$this->status = Lib::type()->timezone_nameabbr($this->result, $timezoneOrNameOrAbbr);

		return $this;
	}


	/**
	 * @return static
	 */
	public function date($datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->date($this->result, $datestring, $timezoneFallback, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function adate($datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->adate($this->result, $datestring, $timezoneFallback, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function idate($datestring, $timezoneFallback = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->idate($this->result, $datestring, $timezoneFallback, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function date_tz($datestring, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->date_tz($this->result, $datestring, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function adate_tz($datestring, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->adate_tz($this->result, $datestring, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function idate_tz($datestring, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->idate_tz($this->result, $datestring, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function date_of(string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->date_of($this->result, $format, $dateFormatted, $timezoneFallback, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function adate_of(string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->adate_of($this->result, $format, $dateFormatted, $timezoneFallback, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function idate_of(string $format, $dateFormatted, $timezoneFallback = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->idate_of($this->result, $format, $dateFormatted, $timezoneFallback, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function date_tz_formatted(string $format, $dateFormatted, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->date_tz_formatted($this->result, $format, $dateFormatted, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function adate_tz_formatted(string $format, $dateFormatted, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->adate_tz_formatted($this->result, $format, $dateFormatted, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function idate_tz_formatted(string $format, $dateFormatted, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->idate_tz_formatted($this->result, $format, $dateFormatted, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function date_microtime($microtime, $timezoneSet = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->date_microtime($this->result, $microtime, $timezoneSet, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function adate_microtime($microtime, $timezoneSet = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->adate_microtime($this->result, $microtime, $timezoneSet, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function idate_microtime($microtime, $timezoneSet = null, array $allowedTimezoneTypes = null)
	{
		$this->status = Lib::type()->idate_microtime($this->result, $microtime, $timezoneSet, $allowedTimezoneTypes);

		return $this;
	}


	/**
	 * @return static
	 */
	public function interval($interval)
	{
		$this->status = Lib::type()->interval($this->result, $interval);

		return $this;
	}


	/**
	 * @return static
	 */
	public function interval_duration($duration)
	{
		$this->status = Lib::type()->interval_duration($this->result, $duration);

		return $this;
	}


	/**
	 * @return static
	 */
	public function interval_datestring($datestring)
	{
		$this->status = Lib::type()->interval_datestring($this->result, $datestring);

		return $this;
	}


	/**
	 * @return static
	 */
	public function interval_microtime($microtime)
	{
		$this->status = Lib::type()->interval_microtime($this->result, $microtime);

		return $this;
	}


	/**
	 * @return static
	 */
	public function interval_ago($date, \DateTimeInterface $from = null, bool $reverse = null)
	{
		$this->status = Lib::type()->interval_ago($this->result, $date, $from, $reverse);

		return $this;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return static
	 */
	public function struct_exists($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_exists($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return static
	 */
	public function struct($value, int $flags = null)
	{
		$this->status = Lib::type()->struct($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return static
	 */
	public function struct_class($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_class($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_interface($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_interface($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_trait($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_trait($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @template-covariant T of \UnitEnum
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return static
	 */
	public function struct_enum($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_enum($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @template-covariant T of object
	 *
	 * @param class-string<T>|T|mixed $value
	 *
	 * @return static
	 */
	public function struct_fqcn($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_fqcn($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_namespace($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_namespace($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_basename($value, int $flags = null)
	{
		$this->status = Lib::type()->struct_basename($this->result, $value, $flags);

		return $this;
	}


	/**
	 * @return static
	 */
	public function resource($value)
	{
		$this->status = Lib::type()->resource($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function resource_opened($value)
	{
		$this->status = Lib::type()->resource_opened($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function resource_closed($value)
	{
		$this->status = Lib::type()->resource_closed($this->result, $value);

		return $this;
	}


	/**
	 * @template-covariant T of \UnitEnum
	 *
	 * @param T|int|string         $value
	 * @param class-string<T>|null $enumClass
	 *
	 * @return class-string|null
	 *
	 * @return static
	 */
	public function enum_case($value, string $enumClass = null)
	{
		$this->status = Lib::type()->enum_case($this->result, $value, $enumClass);

		return $this;
	}


	/**
	 * @return static
	 */
	public function method_array($value)
	{
		$this->status = Lib::type()->method_array($this->result, $value);

		return $this;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return static
	 */
	public function method_string($value, array $refs = [])
	{
		$this->status = Lib::type()->method_string($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @param string|object $newScope
	 *
	 * @return static
	 */
	public function callable($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_object($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_object($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_object_closure($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_object_closure($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_object_invokable($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_object_invokable($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @param string|object                                            $newScope
	 *
	 * @return static
	 */
	public function callable_array($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_array($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @param string|object                                            $newScope
	 *
	 * @return static
	 */
	public function callable_array_method($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_array_method($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @param string|object                                     $newScope
	 *
	 * @return static
	 */
	public function callable_array_method_static($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_array_method_static($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @param string|object                               $newScope
	 *
	 * @return static
	 */
	public function callable_array_method_non_static($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_array_method_non_static($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_string($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_string($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_string_function($value)
	{
		$this->status = Lib::type()->callable_string_function($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_string_function_internal($value)
	{
		$this->status = Lib::type()->callable_string_function_internal($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_string_function_non_internal($value)
	{
		$this->status = Lib::type()->callable_string_function_non_internal($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function callable_string_method_static($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_string_method_static($this->result, $value, $newScope);

		return $this;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return static
	 */
	public function path($value, array $refs = [])
	{
		$this->status = Lib::type()->path($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return static
	 */
	public function realpath($value, bool $allowSymlink = null, array $refs = [])
	{
		$this->status = Lib::type()->realpath($this->result, $value, $allowSymlink, $refs);

		return $this;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return static
	 */
	public function dirpath($value, bool $allowExists = null, bool $allowSymlink = null, array $refs = [])
	{
		$this->status = Lib::type()->dirpath($this->result, $value, $allowExists, $allowSymlink, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function filepath($value, bool $allowExists = null, bool $allowSymlink = null, array $refs = [])
	{
		$this->status = Lib::type()->filepath($this->result, $value, $allowExists, $allowSymlink, $refs);

		return $this;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return static
	 */
	public function dirpath_realpath($value, bool $allowSymlink = null, array $refs = [])
	{
		$this->status = Lib::type()->dirpath_realpath($this->result, $value, $allowSymlink, $refs);

		return $this;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 *
	 * @return static
	 */
	public function filepath_realpath($value, bool $allowSymlink = null, array $refs = [])
	{
		$this->status = Lib::type()->filepath_realpath($this->result, $value, $allowSymlink, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function filename($value)
	{
		$this->status = Lib::type()->filename($this->result, $value);

		return $this;
	}
}
