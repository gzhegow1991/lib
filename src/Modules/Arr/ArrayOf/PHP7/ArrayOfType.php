<?php

namespace Gzhegow\Lib\Modules\Arr\ArrayOf\PHP7;


class ArrayOfType extends ArrayOf
{
    public function __construct(
        $type = [ 'mixed' => 'mixed' ],
        array $options = []
    )
    {
        $options = []
            + [ 'isOfType' => true ]
            + $options;

        parent::__construct($type, null, $options);
    }
}
