<?php

/**
 * This class is autogenerated.
 */

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Bcmath\Bcnumber;
use Gzhegow\Lib\Modules\Crypt\Alphabet;
use Gzhegow\Lib\Modules\Type\Base\ParseModuleBase;

class ParseModule extends ParseModuleBase
{
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
	public function numeric_int($value)
	{
		if (Lib::type()->numeric_int($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_non_zero($value)
	{
		if (Lib::type()->numeric_int_non_zero($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_non_negative($value)
	{
		if (Lib::type()->numeric_int_non_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_non_positive($value)
	{
		if (Lib::type()->numeric_int_non_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_negative($value)
	{
		if (Lib::type()->numeric_int_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_int_positive($value)
	{
		if (Lib::type()->numeric_int_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric($value)
	{
		if (Lib::type()->numeric($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_non_zero($value)
	{
		if (Lib::type()->numeric_non_zero($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_non_negative($value)
	{
		if (Lib::type()->numeric_non_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_non_positive($value)
	{
		if (Lib::type()->numeric_non_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_negative($value)
	{
		if (Lib::type()->numeric_negative($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function numeric_positive($value)
	{
		if (Lib::type()->numeric_positive($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return Bcnumber|null
	 */
	public function bcnum($value)
	{
		if (Lib::type()->bcnum($result, $value)) {
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
	public function trim($value, string $characters = null)
	{
		if (Lib::type()->trim($result, $value, $characters)) {
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
	public function ctype_alpha($value, bool $ignoreCase = null)
	{
		if (Lib::type()->ctype_alpha($result, $value, $ignoreCase)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function ctype_alnum($value, bool $ignoreCase = null)
	{
		if (Lib::type()->ctype_alnum($result, $value, $ignoreCase)) {
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
	public function list_strict($value)
	{
		if (Lib::type()->list_strict($result, $value)) {
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
	public function dict_strict($value)
	{
		if (Lib::type()->dict_strict($result, $value)) {
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
	 * @return string|null
	 */
	public function ip($value)
	{
		if (Lib::type()->ip($result, $value)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param string            $value
	 * @param string|array|null $query
	 * @param string|null       $fragment
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
	 * @param string      $value
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
	 * @param string            $value
	 * @param string|array|null $query
	 * @param string|null       $fragment
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
	 * @param callable          ...$fnExistsList
	 * @return class-string|null
	 */
	public function struct($value, bool $useRegex = null, ...$fnExistsList)
	{
		if (Lib::type()->struct($result, $value, $useRegex, ...$fnExistsList)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return class-string|null
	 */
	public function struct_class($value, bool $useRegex = null)
	{
		if (Lib::type()->struct_class($result, $value, $useRegex)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return class-string|null
	 */
	public function struct_interface($value, bool $useRegex = null)
	{
		if (Lib::type()->struct_interface($result, $value, $useRegex)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return class-string|null
	 */
	public function struct_trait($value, bool $useRegex = null)
	{
		if (Lib::type()->struct_trait($result, $value, $useRegex)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param callable          ...$fnExistsList
	 * @return class-string|null
	 */
	public function struct_fqcn($value, bool $useRegex = null, ...$fnExistsList)
	{
		if (Lib::type()->struct_fqcn($result, $value, $useRegex, ...$fnExistsList)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param callable    ...$fnExistsList
	 * @return string|null
	 */
	public function struct_namespace($value, bool $useRegex = null, ...$fnExistsList)
	{
		if (Lib::type()->struct_namespace($result, $value, $useRegex, ...$fnExistsList)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param callable    ...$fnExistsList
	 * @return string|null
	 */
	public function struct_basename($value, bool $useRegex = null, ...$fnExistsList)
	{
		if (Lib::type()->struct_basename($result, $value, $useRegex, ...$fnExistsList)) {
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
	public function callable_string_function($value, $newScope = 'static')
	{
		if (Lib::type()->callable_string_function($result, $value, $newScope)) {
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
	 * @return string|null
	 */
	public function dirpath($value, array $refs = [])
	{
		if (Lib::type()->dirpath($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @return string|null
	 */
	public function filepath($value, array $refs = [])
	{
		if (Lib::type()->filepath($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 * @return string|null
	 */
	public function path_realpath($value, array $refs = [])
	{
		if (Lib::type()->path_realpath($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 * @return string|null
	 */
	public function dirpath_realpath($value, array $refs = [])
	{
		if (Lib::type()->dirpath_realpath($result, $value, $refs)) {
		    return $result;
		}

		return null;
	}


	/**
	 * @param array{ 0: array|null } $refs
	 * @return string|null
	 */
	public function filepath_realpath($value, array $refs = [])
	{
		if (Lib::type()->filepath_realpath($result, $value, $refs)) {
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
}
