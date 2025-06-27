<?php

namespace Gzhegow\Lib\Modules\Http\Session\SessionDisabler\PHP7;

use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Exception\RuntimeException;


class SessionDisabler implements \ArrayAccess, \Countable
{
    public function offsetExists($offset) : bool
    {
        /** @see HttpModule::session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetGet($offset)
    {
        /** @see HttpModule::session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetSet($offset, $value) : void
    {
        /** @see HttpModule::session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetUnset($offset) : void
    {
        /** @see HttpModule::session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }


    public function count() : int
    {
        /** @see HttpModule::session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }
}
