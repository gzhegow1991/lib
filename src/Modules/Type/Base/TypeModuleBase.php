<?php

namespace Gzhegow\Lib\Modules\Type\Base;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;


if (! defined('_TYPE_NIL')) define('_TYPE_NIL', '{N}');
if (! defined('_TYPE_UNDEFINED')) define('_TYPE_UNDEFINED', NAN);

abstract class TypeModuleBase
{
    const TYPE_NIL       = _TYPE_NIL;
    const TYPE_UNDEFINED = _TYPE_UNDEFINED;


    /**
     * @var callable
     */
    protected $fnIsUndefined;


    public function static_fn_is_undefined($fnIsUndefined = null) // : ?mixed
    {
        if (null !== $fnIsUndefined) {
            $last = $this->fnIsUndefined;

            if (! is_callable($fnIsUndefined)) {
                throw new LogicException(
                    'The `fnIsUndefined` should be callable'
                );
            }

            $this->fnIsUndefined = $fnIsUndefined;

            $result = $last;
        }

        $result = $result ?? $this->fnIsUndefined;

        return $result;
    }


    public function the_nil()
    {
        return _TYPE_NIL;
    }

    public function the_undefined()
    {
        return _TYPE_UNDEFINED;
    }


    public function the_decimal_point() : string
    {
        return localeconv()[ 'decimal_point' ];
    }


    public function is_null($value) : bool
    {
        return null === $value;
    }

    public function is_not_null($value) : bool
    {
        return null !== $value;
    }


    public function is_false($value) : bool
    {
        return false === $value;
    }

    public function is_not_false($value) : bool
    {
        return false !== $value;
    }


    public function is_nan($value) : bool
    {
        return is_float($value) && is_nan($value);
    }

    public function is_not_nan($value) : bool
    {
        return ! (is_float($value) && is_nan($value));
    }


    public function is_empty($value) : bool
    {
        return empty($value);
    }

    public function is_not_empty($value) : bool
    {
        return ! empty($value);
    }


    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     */
    public function is_nil($value) : bool
    {
        if ($this->the_nil() === $value) {
            return true;
        }

        return false;
    }

    public function is_not_nil($value) : bool
    {
        $result = null;

        if ($this->the_nil() === $value) {
            return false;
        }

        $result = $value;

        return true;
    }


    /**
     * > Специальный тип, который значит, что свойство объекта ещё не имеет установленного значения (если NULL - это допустимое значение)
     * > Можно (и лучше) использовать пустой массив, если нулевой ключ есть - то передали, иначе - не передали
     */
    public function is_undefined($value) : bool
    {
        $fn = $this->static_fn_is_undefined();

        if (null !== $fn) {
            return $fn($value);
        }

        return $this->fn_is_undefined($value);
    }

    public function is_not_undefined($value) : bool
    {
        return ! $this->is_undefined($value);
    }

    protected function fn_is_undefined($value) : bool
    {
        return is_float($value) && is_nan($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно безопасно заменить NULL-ом
     */
    public function is_nullable($value) : bool
    {
        // > EMPTY STRING is not nullable
        if ('' === $value) {
            return false;
        }

        if (false
            // > NULL is nullable
            || (null === $value)
            //
            // > NAN is nullable
            || $this->is_nan($value)
            //
            // > NIL is nullable
            || $this->is_nil($value)
        ) {
            return true;
        }

        return false;
    }

    public function is_not_nullable($value) : bool
    {
        if ($this->is_nullable($value)) {
            return false;
        }

        return true;
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     */
    public function is_blank($value) : bool
    {
        $result = null;

        if ('' === $value) {
            // > EMPTY STRING is blank
            return true;
        }

        if ($this->is_nullable($value)) {
            // > NULLABLE is blank
            return true;
        }

        if (is_scalar($value)) {
            // > BOOLEAN is not blank
            // > INTEGER is not blank
            // > FLOAT is not blank
            // > STRING (including '0') is not blank

            return false;
        }

        if (empty($value)) {
            // > EMPTY ARRAY is blank

            return true;
        }

        if (is_object($value)) {
            if (null === ($cnt = Lib::php()->count($value))) {
                return false;
            }

            if (0 === $cnt) {
                return true;
            }
        }

        return false;
    }

    public function is_not_blank($value) : bool
    {
        if ($this->is_blank($value)) {
            return false;
        }

        return true;
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из кода
     */
    public function is_passed($value) : bool
    {
        if ($this->is_nil($value)) {
            // > NIL is passed
            return true;
        }

        if (! $this->is_nullable($value)) {
            // > NULLABLE is not passed (except NIL)
            return true;
        }

        return false;
    }

    public function is_not_passed($value) : bool
    {
        return ! $this->is_passed($value);
    }
}
