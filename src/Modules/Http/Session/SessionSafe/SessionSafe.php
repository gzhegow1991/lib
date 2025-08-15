<?php

namespace Gzhegow\Lib\Modules\Http\Session\SessionSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class SessionSafe
{
    /**
     * @param array{ 0?: mixed } $refs
     */
    public function has(string $key, array $refs = []) : bool
    {
        $withValue = array_key_exists(0, $refs);
        if ($withValue) {
            $refValue =& $refs[ 0 ];
        }
        $refValue = null;

        if (array_key_exists($key, $_SESSION)) {
            $refValue = $_SESSION[ $key ];

            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function get(string $key, array $fallback = [])
    {
        if (! array_key_exists($key, $_SESSION)) {
            if ([] !== $fallback) {
                return $fallback[ 0 ];
            }

            throw new RuntimeException(
                [ "Missing session key: \$_SESSION[{$key}]", $key ]
            );
        }

        return $_SESSION[ $key ];
    }

    /**
     * @return static
     */
    public function set(string $key, $value)
    {
        $_SESSION[ $key ] = $value;

        return $this;
    }

    /**
     * @return static
     */
    public function unset(string $key)
    {
        unset($_SESSION[ $key ]);

        return $this;
    }

    /**
     * @return static
     */
    public function clear()
    {
        $_SESSION = [];

        return $this;
    }


    /**
     * @return mixed
     */
    public function call_safe(\Closure $fn, array $args = [])
    {
        $theFunc = Lib::func();

        $beforeErrorReporting = error_reporting(E_ALL | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler([ $theFunc, 'safe_call_error_handler' ]);

        $result = call_user_func_array($fn, $args);

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }
}
