<?php

namespace Gzhegow\Lib\Modules\Http\Session\PHP8;

use Gzhegow\Lib\Modules\HttpModule;
use Gzhegow\Lib\Exception\RuntimeException;


class SessionDisabler implements \ArrayAccess, \Countable
{
    public function offsetExists($offset) : bool
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetGet($offset) : mixed
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetSet($offset, $value) : void
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetUnset($offset) : void
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }


    public function count() : int
    {
        /** @see HttpModule::static_session() */

        throw new RuntimeException('Native $_SESSION is disabled');
    }
}
