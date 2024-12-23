<?php

namespace Gzhegow\Lib\ArrayOf;


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
