<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\Base;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Arr\ArrayOf\Base\AbstractArrayOf;


abstract class AbstractArrayOfType extends AbstractArrayOf
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
