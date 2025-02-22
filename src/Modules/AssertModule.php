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
	public function null($value)
	{
		$this->status = Lib::type()->null($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_null($value)
	{
		$this->status = Lib::type()->not_null($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function false($value)
	{
		$this->status = Lib::type()->false($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_false($value)
	{
		$this->status = Lib::type()->not_false($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function nan($value)
	{
		$this->status = Lib::type()->nan($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_nan($value)
	{
		$this->status = Lib::type()->not_nan($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function empty($value)
	{
		$this->status = Lib::type()->empty($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_empty($value)
	{
		$this->status = Lib::type()->not_empty($this->result, $value);

		return $this;
	}


	/**
	 * > NULL переданный пользователем через API, например '{N}'
	 *
	 * @return static
	 */
	public function nil($value)
	{
		$this->status = Lib::type()->nil($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_nil($value)
	{
		$this->status = Lib::type()->not_nil($this->result, $value);

		return $this;
	}


	/**
	 * > Специальный тип, что свойство объекта ещё не имеет значения (если NULL - это допустимое значение)
	 *
	 * @return static
	 */
	public function undefined($value)
	{
		$this->status = Lib::type()->undefined($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_undefined($value)
	{
		$this->status = Lib::type()->not_undefined($this->result, $value);

		return $this;
	}


	/**
	 * > Значение можно безопасно заменить NULL-ом
	 *
	 * > NULL is nullable
	 * > NAN is nullable
	 * > NIL is nullable
	 *
	 * > '' is not nullable
	 *
	 * @return static
	 */
	public function nullable($value)
	{
		$this->status = Lib::type()->nullable($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_nullable($value)
	{
		$this->status = Lib::type()->not_nullable($this->result, $value);

		return $this;
	}


	/**
	 * > Значение можно отбросить или не учитывать, т.к. оно не несёт информации
	 *
	 * > empty string is blank
	 * > nullable is blank
	 * > empty array is blank
	 * > empty countable object is blank
	 *
	 * > '0' is not blank
	 *
	 * @return static
	 */
	public function blank($value)
	{
		$this->status = Lib::type()->blank($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_blank($value)
	{
		$this->status = Lib::type()->not_blank($this->result, $value);

		return $this;
	}


	/**
	 * Значение было отправлено пользователем
	 * Если в АПИ пришло NULL - значит стоит "не трогать", а если NIL - значит надо "удалить"
	 *
	 * > nil is passed
	 * > any non-nullable is passed
	 *
	 * @return static
	 */
	public function passed($value)
	{
		$this->status = Lib::type()->passed($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function not_passed($value)
	{
		$this->status = Lib::type()->not_passed($this->result, $value);

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
	public function numeric_int($value)
	{
		$this->status = Lib::type()->numeric_int($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_non_zero($value)
	{
		$this->status = Lib::type()->numeric_int_non_zero($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_non_negative($value)
	{
		$this->status = Lib::type()->numeric_int_non_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_non_positive($value)
	{
		$this->status = Lib::type()->numeric_int_non_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_negative($value)
	{
		$this->status = Lib::type()->numeric_int_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_int_positive($value)
	{
		$this->status = Lib::type()->numeric_int_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric($value)
	{
		$this->status = Lib::type()->numeric($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_non_zero($value)
	{
		$this->status = Lib::type()->numeric_non_zero($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_non_negative($value)
	{
		$this->status = Lib::type()->numeric_non_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_non_positive($value)
	{
		$this->status = Lib::type()->numeric_non_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_negative($value)
	{
		$this->status = Lib::type()->numeric_negative($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function numeric_positive($value)
	{
		$this->status = Lib::type()->numeric_positive($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function bcnum($value)
	{
		$this->status = Lib::type()->bcnum($this->result, $value);

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
	public function ctype_digit($value)
	{
		$this->status = Lib::type()->ctype_digit($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function ctype_alpha($value, bool $ignoreCase = null)
	{
		$this->status = Lib::type()->ctype_alpha($this->result, $value, $ignoreCase);

		return $this;
	}


	/**
	 * @return static
	 */
	public function ctype_alnum($value, bool $ignoreCase = null)
	{
		$this->status = Lib::type()->ctype_alnum($this->result, $value, $ignoreCase);

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
	public function list_strict($value)
	{
		$this->status = Lib::type()->list_strict($this->result, $value);

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
	public function regex($value)
	{
		$this->status = Lib::type()->regex($this->result, $value);

		return $this;
	}


	/**
	 * @return static
	 */
	public function ip($value)
	{
		$this->status = Lib::type()->ip($this->result, $value);

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
	 * @param callable          ...$fnExistsList
	 *
	 * @return static
	 */
	public function struct($value, bool $useRegex = null, ...$fnExistsList)
	{
		$this->status = Lib::type()->struct($this->result, $value, $useRegex, ...$fnExistsList);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_class($value, bool $useRegex = null)
	{
		$this->status = Lib::type()->struct_class($this->result, $value, $useRegex);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_interface($value, bool $useRegex = null)
	{
		$this->status = Lib::type()->struct_interface($this->result, $value, $useRegex);

		return $this;
	}


	/**
	 * @return static
	 */
	public function struct_trait($value, bool $useRegex = null)
	{
		$this->status = Lib::type()->struct_trait($this->result, $value, $useRegex);

		return $this;
	}


	/**
	 * @param callable          ...$fnExistsList
	 *
	 * @return static
	 */
	public function struct_fqcn($value, bool $useRegex = null, ...$fnExistsList)
	{
		$this->status = Lib::type()->struct_fqcn($this->result, $value, $useRegex, ...$fnExistsList);

		return $this;
	}


	/**
	 * @param callable    ...$fnExistsList
	 *
	 * @return static
	 */
	public function struct_namespace($value, bool $useRegex = null, ...$fnExistsList)
	{
		$this->status = Lib::type()->struct_namespace($this->result, $value, $useRegex, ...$fnExistsList);

		return $this;
	}


	/**
	 * @param callable    ...$fnExistsList
	 *
	 * @return static
	 */
	public function struct_basename($value, bool $useRegex = null, ...$fnExistsList)
	{
		$this->status = Lib::type()->struct_basename($this->result, $value, $useRegex, ...$fnExistsList);

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
	 * @return static
	 */
	public function method_string($value)
	{
		$this->status = Lib::type()->method_string($this->result, $value);

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
	public function callable_string_function($value, $newScope = 'static')
	{
		$this->status = Lib::type()->callable_string_function($this->result, $value, $newScope);

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
	 * @return static
	 */
	public function path($value, array $refs = [])
	{
		$this->status = Lib::type()->path($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function dirpath($value, array $refs = [])
	{
		$this->status = Lib::type()->dirpath($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function filepath($value, array $refs = [])
	{
		$this->status = Lib::type()->filepath($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function path_realpath($value, array $refs = [])
	{
		$this->status = Lib::type()->path_realpath($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function dirpath_realpath($value, array $refs = [])
	{
		$this->status = Lib::type()->dirpath_realpath($this->result, $value, $refs);

		return $this;
	}


	/**
	 * @return static
	 */
	public function filepath_realpath($value, array $refs = [])
	{
		$this->status = Lib::type()->filepath_realpath($this->result, $value, $refs);

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
