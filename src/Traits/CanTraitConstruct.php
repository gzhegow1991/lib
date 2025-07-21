<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Lib;


trait CanTraitConstruct
{
    protected function __traitConstruct(array $args = [])
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $traits = $thePhp->class_uses_with_parents($this, true);

        foreach ( $traits as $trait ) {
            if (! $theType->struct_basename($trait)->isOk([ &$traitBasename ])) {
                continue;
            }

            $fn = "__construct{$traitBasename}";

            if (method_exists($this, $fn)) {
                call_user_func_array([ $this, $fn ], $args);
            }
        }
    }
}
