<?php

namespace Gzhegow\Lib\Modules\Type\Base;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Nil;


abstract class TypeModuleBase
{
    public function the_nil() : string
    {
        return new Nil();
    }


    public function the_timezone_nil() : \DateTimeZone
    {
        return new \DateTimeZone('+1234');
    }


    public function the_decimal_point() : string
    {
        return localeconv()[ 'decimal_point' ];
    }


    public function is_empty($value) : bool
    {
        return empty($value);
    }

    public function is_not_empty($value) : bool
    {
        return ! empty($value);
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


    public function is_finite($value) : bool
    {
        return is_float($value) && is_finite($value);
    }

    public function is_not_finite($value) : bool
    {
        return ! (is_float($value) && is_finite($value));
    }


    public function is_empty_string($value) : bool
    {
        return '' === $value;
    }

    public function is_not_empty_string($value) : bool
    {
        return '' !== $value;
    }


    public function is_empty_array($value) : bool
    {
        return [] === $value;
    }

    public function is_not_empty_array($value) : bool
    {
        return [] !== $value;
    }


    public function is_closed_resource($value) : bool
    {
        return 'resource (closed)' === gettype($value);
    }

    public function is_not_closed_resource($value) : bool
    {
        return 'resource (closed)' !== gettype($value);
    }


    /**
     * > Специальный тип-синоним NULL, переданный пользователем через API, например '{N}'
     * > в случаях, когда NULL интерпретируется как "не трогать", а NIL как "очистить"
     *
     * > NAN не равен ничему даже самому себе
     * > NIL равен только самому себе
     * > NULL означает пустоту и им можно заменить значения '', [], `resource (closed)`, NIL, но нельзя заменить NAN
     */
    public function is_nil($value) : bool
    {
        return Nil::is($value);
    }

    public function is_not_nil($value) : bool
    {
        return ! Nil::is($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно безопасно заменить NULL-ом
     */
    public function is_nullable($value) : bool
    {
        // > NAN is not nullable
        // > EMPTY ARRAY is not nullable

        if (
            // > NULL is nullable
            (null === $value)
            //
            // > EMPTY STRING is nullable
            || ('' === $value)
            //
            // > CLOSED RESOURCE is nullable
            || ('resource (closed)' === gettype($value))
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
        return ! $this->is_nullable($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     */
    public function is_blank($value) : bool
    {
        // > NAN is not blank
        // > NIL is not blank

        if (
            // > NULL is blank
            (null === $value)
            //
            // > EMPTY STRING is blank
            || ('' === $value)
            //
            // > EMPTY ARRAY is blank
            || ([] === $value)
            //
            // > CLOSED RESOURCE is blank
            || ('resource (closed)' === gettype($value))
        ) {
            return true;
        }

        if (is_scalar($value)) {
            // > BOOLEAN is not blank
            // > INTEGER is not blank
            // > FLOAT is not blank
            // > STRING (including '0', excluding '') is not blank

            return false;
        }

        if (is_object($value)) {
            $cnt = Lib::php()->count($value);

            if (is_float($cnt) && is_nan($cnt)) {
                // > NON-COUNTABLE is not blank
                return false;
            }

            if (0 === $cnt) {
                // > COUNTABLE w/ ZERO SIZE is blank
                return true;
            }
        }

        return false;
    }

    public function is_not_blank($value) : bool
    {
        return ! $this->is_blank($value);
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось из кода
     */
    public function is_passed($value) : bool
    {
        // > NIL is passed
        // > EMPTY ARRAY is passed
        // > EMPTY COUNTABLE is passed

        if (null === $value) {
            // > NULL is not passed
            return false;
        }

        if ('' === $value) {
            // > EMPTY STRING is not passed
            return false;
        }

        if (is_float($value) && is_nan($value)) {
            // > NAN is not passed
            return false;
        }

        if ('resource (closed)' === gettype($value)) {
            // > CLOSED RESOURCE is not passed
            return false;
        }

        return true;
    }

    public function is_not_passed($value) : bool
    {
        return ! $this->is_passed($value);
    }
}
