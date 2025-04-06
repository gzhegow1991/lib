<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;


abstract class AbstractListOfType extends AbstractListOf
{
    public function set($key, $value)
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

        return parent::set($key, $value);
    }
}
