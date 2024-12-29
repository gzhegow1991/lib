<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Lib;


trait CanTraitConstruct
{
    protected function __traitConstruct(array $args = [])
    {
        $theParse = Lib::parse();
        $thePhp = Lib::php();

        $traits = $thePhp->class_uses_with_parents($this, true);

        foreach ( $traits as $trait ) {
            $fn = '__construct' . $theParse->struct_basename($trait);

            if (method_exists($this, $fn)) {
                call_user_func_array([ $this, $fn ], $args);
            }
        }
    }
}
