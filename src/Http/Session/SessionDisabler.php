<?php

namespace Gzhegow\Lib\Http\Session;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class SessionDisabler implements \ArrayAccess, \Countable
{
    public function offsetExists($offset)
    {
        /** @see Lib::http_session() */
        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetGet($offset)
    {
        /** @see Lib::http_session() */
        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetSet($offset, $value)
    {
        /** @see Lib::http_session() */
        throw new RuntimeException('Native $_SESSION is disabled');
    }

    public function offsetUnset($offset)
    {
        /** @see Lib::http_session() */
        throw new RuntimeException('Native $_SESSION is disabled');
    }


    public function count()
    {
        /** @see Lib::http_session() */
        throw new RuntimeException('Native $_SESSION is disabled');
    }
}
