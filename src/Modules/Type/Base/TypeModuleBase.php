<?php

namespace Gzhegow\Lib\Modules\Type\Base;


if (! defined('_TYPE_DECIMAL_POINT')) define('_TYPE_DECIMAL_POINT', localeconv()[ 'decimal_point' ]);
if (! defined('_TYPE_NIL')) define('_TYPE_NIL', '{N}');
if (! defined('_TYPE_UNDEFINED')) define('_TYPE_UNDEFINED', NAN);

abstract class TypeModuleBase
{
    const DECIMAL_POINT = _TYPE_DECIMAL_POINT;
    const NIL           = _TYPE_NIL;
    const UNDEFINED     = _TYPE_UNDEFINED;


    public function the_decimal_point() : string
    {
        return _TYPE_DECIMAL_POINT;
    }

    public function the_nil()
    {
        return _BOOL_NIL;
    }

    public function the_undefined()
    {
        return _BOOL_UNDEFINED;
    }
}
