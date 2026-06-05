<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\RuntimeException;


class EntrypointPhpSessionCookieParamsDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        $theHttpSession = Lib::httpSession();

        return $theHttpSession->session_get_cookie_params();
    }

    public function getRecommended()
    {
        return [
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->php_array($value)->orThrow();

        $valueValidScheme = [
            'lifetime' => true,
            'path'     => true,
            'domain'   => true,
            'secure'   => true,
            'httponly' => true,
            'samesite' => true,
        ];

        if ( array_diff_key($valueValid, $valueValidScheme) ) {
            throw new RuntimeException(
                [ 'The `value` should be with keys by scheme', $valueValid, $valueValidScheme ]
            );
        }

        $configSet[EntrypointModule::OPT_PHP_SESSION_COOKIE_PARAMS] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        $theHttpSession = Lib::httpSession();

        $theHttpSession->session_set_cookie_params($value);
    }
}
