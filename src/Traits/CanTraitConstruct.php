<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Lib;


trait CanTraitConstruct
{
    protected function __traitConstruct(array $args = [])
    {
        $theType = Lib::type();

        $traits = Lib::php()->class_uses_with_parents($this, true);

        foreach ( $traits as $trait ) {
            if (! $theType->struct_basename($traitBasename, $trait)) {
                continue;
            }

            $fn = "__construct{$traitBasename}";

            if (method_exists($this, $fn)) {
                call_user_func_array([ $this, $fn ], $args);
            }
        }
    }
}
