<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


abstract class AbstractListOfType extends AbstractListOf
{
    protected function setValue($key, $value)
    {
        if ( null !== $this->valueType ) {
            if ( $this->valueType !== gettype($value) ) {
                throw new LogicException(
                    [
                        'The `value` should be a value of type: ' . $this->valueType,
                        $value,
                    ]
                );
            }
        }

        return parent::setValue($key, $value);
    }
}
