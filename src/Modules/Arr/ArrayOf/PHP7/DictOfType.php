<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7;


class DictOfType extends DictOf
{
    public function __construct(
        string $valueType,
        array $options = []
    )
    {
        $options = []
            + [ 'isOfType' => true ]
            + $options;

        parent::__construct($valueType, null, $options);
    }
}
