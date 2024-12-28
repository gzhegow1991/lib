<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Lib;


trait CanTraitConstruct
{
    public function __traitConstruct(...$args)
    {
        $theParse = Lib::parse();
        $thePhp = Lib::php();

        $traits = $thePhp->class_uses_with_parents($this, true);

        foreach ( $traits as $trait ) {
            $fn = '__construct' . $theParse->struct_basename($trait);

            if (method_exists($this, $fn)) {
                call_user_func([ $this, $fn ], ...$args);
            }
        }
    }
}
