<?php

namespace Gzhegow\Lib\Modules\Type\Base;

use Gzhegow\Lib\Modules\Type\Nil;


abstract class TypeModuleBase
{
    public function the_decimal_point() : string
    {
        return localeconv()[ 'decimal_point' ];
    }


    public function the_nil() : Nil
    {
        return new Nil();
    }

    public function the_timezone_nil() : \DateTimeZone
    {
        return new \DateTimeZone('+1234');
    }


    public function is_empty($value) : bool
    {
        return empty($value);
    }

    public function is_any_not_empty($value) : bool
    {
        return ! empty($value);
    }


    public function is_null($value) : bool
    {
        return null === $value;
    }

    public function is_any_not_null($value) : bool
    {
        return null !== $value;
    }


    public function is_false($value) : bool
    {
        return false === $value;
    }

    public function is_bool_not_false($value) : bool
    {
        return true === $value;
    }

    public function is_any_not_false($value) : bool
    {
        return false !== $value;
    }


    public function is_true($value) : bool
    {
        return true === $value;
    }

    public function is_bool_not_true($value) : bool
    {
        return false === $value;
    }

    public function is_any_not_true($value) : bool
    {
        return true !== $value;
    }


    public function is_nan($value) : bool
    {
        return is_float($value) && is_nan($value);
    }

    public function is_float_not_nan($value) : bool
    {
        return is_float($value) && ! is_nan($value);
    }

    public function is_any_not_nan($value) : bool
    {
        return ! (is_float($value) && is_nan($value));
    }


    public function is_finite($value) : bool
    {
        return is_float($value) && is_finite($value);
    }

    public function is_float_not_finite($value) : bool
    {
        return is_float($value) && ! is_finite($value);
    }

    public function is_any_not_finite($value) : bool
    {
        return ! (is_float($value) && is_finite($value));
    }


    public function is_infinite($value) : bool
    {
        return is_float($value) && is_infinite($value);
    }

    public function is_float_not_infinite($value) : bool
    {
        return is_float($value) && ! is_infinite($value);
    }

    public function is_any_not_infinite($value) : bool
    {
        return ! (is_float($value) && is_infinite($value));
    }


    public function is_string_empty($value) : bool
    {
        return '' === $value;
    }

    public function is_string_not_empty($value) : bool
    {
        return is_string($value) && ('' !== $value);
    }

    public function is_any_not_string_empty($value) : bool
    {
        return '' !== $value;
    }


    public function is_array_empty($value) : bool
    {
        return [] === $value;
    }

    public function is_array_not_empty($value) : bool
    {
        return is_array($value) && ([] !== $value);
    }

    public function is_any_not_array_empty($value) : bool
    {
        return [] !== $value;
    }


    public function is_resource($value) : bool
    {
        return is_resource($value)
            || 'resource (closed)' === gettype($value);
    }

    public function is_any_not_resource($value) : bool
    {
        return ! (
            is_resource($value)
            || 'resource (closed)' === gettype($value)
        );
    }


    public function is_resource_opened($value) : bool
    {
        return is_resource($value);
    }

    public function is_resource_not_opened($value) : bool
    {
        return 'resource (closed)' === gettype($value);
    }

    public function is_any_not_resource_opened($value) : bool
    {
        return ! is_resource($value);
    }


    public function is_resource_closed($value) : bool
    {
        return 'resource (closed)' === gettype($value);
    }

    public function is_resource_not_closed($value) : bool
    {
        return is_resource($value);
    }

    public function is_any_not_resource_closed($value) : bool
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

    public function is_any_not_nil($value) : bool
    {
        return ! Nil::is($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно отбросить или не учитывать, т.к. оно не несёт информации
     */
    public function is_blank($value) : bool
    {
        if (
            // > NULL is blank (can appear from API to omit any actions on the value)
            (null === $value)
            //
            // > EMPTY STRING is blank (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > EMPTY ARRAY is blank (can appear from HTML forms with no checkbox/radio/select items choosen)
            || ([] === $value)
            //
            //
            // // > CLOSED RESOURCE is not blank (actually its still internal object)
            // || ('resource (closed)' === gettype($value))
            //
            // // > NAN is not blank (NAN equals nothing even itself)
            // || (is_float($value) && is_nan($value))
            //
            // // > NIL is not blank (NIL is passed manually, that literally means NOT BLANK)
            // || $this->is_nil($value)
        ) {
            return true;
        }

        // > COUNTABLE w/ ZERO SIZE is blank
        if ($this->countable($countable, $value)) {
            if (0 === count($countable)) {
                return true;
            }
        }

        return false;
    }

    public function is_any_not_blank($value) : bool
    {
        return ! $this->is_blank($value);
    }


    /**
     * > Специальный тип, который значит, что значение можно заменить NULL-ом
     */
    public function is_clearable($value) : bool
    {
        if (
            // > NULL is clearable (means nothing)
            (null === $value)
            //
            // > EMPTY STRING is clearable (can appear from HTML forms with no input provided)
            || ('' === $value)
            //
            // > CLOSED RESOURCE is clearable (this is the internal garbage with no possible purpose)
            || ('resource (closed)' === gettype($value))
            //
            // > NIL is clearable (NIL should be replaced with NULL later)
            || $this->is_nil($value)
            //
            //
            // // > EMPTY ARRAY is not clearable (array functions is not applicable to nulls)
            // || ([] === $value)
            //
            // // > NAN is not clearable (NAN means some error in the code and shouldnt be replaced)
            // || (is_float($value) && is_nan($value))
        ) {
            return true;
        }

        // // > COUNTABLE w/ ZERO SIZE is not nullable (countable/iterable functions is not applicable to nulls)
        // if ($this->countable($countable, $value)) {
        //     if (0 === count($countable)) {
        //         return true;
        //     }
        // }

        return false;
    }

    public function is_any_not_clearable($value) : bool
    {
        return ! $this->is_clearable($value);
    }


    /**
     * > Специальный тип, который значит, что значение было отправлено пользователем, а не появилось само
     */
    public function is_passed($value) : bool
    {
        if (
            // > NULL is not passed (can appear from API to omit any actions on the value)
            (null === $value)
            //
            // > EMPTY STRING is not passed (can appear from HTML form with no input provided)
            || ('' === $value)
            //
            // > EMPTY ARRAY is not passed (can appear from HTML forms with no checkbox/radio/select items choosen)
            || ([] === $value)
            //
            // > RESOURCE not passed (user cannot send resource)
            || (is_resource($value) || ('resource (closed)' === gettype($value)))
            //
            // > NAN, -INF, INF is not passed (user cannot send NAN, -INF, +INF)
            || (is_float($value) && ! is_finite($value))
            //
            //
            // // > NIL is passed (NIL is passed manually, that literally means PASSED)
            // || $this->is_nil($value)
        ) {
            return false;
        }

        return true;
    }

    public function is_any_not_passed($value) : bool
    {
        return ! $this->is_passed($value);
    }
}
