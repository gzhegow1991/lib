<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


abstract class AbstractArrayOfType extends AbstractArrayOf
{
    protected function setValue($key, $value)
    {
        if (null !== $this->valueType) {
            if ($this->valueType !== gettype($value)) {
                throw new LogicException(
                    [
                        'The `value` should be of type: ' . $this->valueType,
                        $value,
                    ]
                );
            }
        }

        return parent::setValue($key, $value);
    }
}
