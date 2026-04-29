<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


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

        $valueValid = $theType->array($value)->orThrow();

        $valueValidScheme = [
            'lifetime' => true,
            'path'     => true,
            'domain'   => true,
            'secure'   => true,
            'httponly' => true,
            'samesite' => true,
        ];
        $valueValid = array_intersect_key($valueValid, $valueValidScheme);

        $valueValidKeys = array_keys($valueValidScheme);
        $valueValid = $theType->keys_exists($valueValidKeys, $valueValid)->orThrow();

        $configSet[EntrypointModule::OPT_PHP_SESSION_COOKIE_PARAMS] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        $theHttpSession = Lib::httpSession();

        $theHttpSession->session_set_cookie_params($value);
    }
}
