<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;
use Gzhegow\Lib\Exception\RuntimeException;


class EntrypointPhpDateTimezoneDefaultDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        try {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException($e);
        }

        return $timezone;
    }

    public function getRecommended()
    {
        return new \DateTimeZone('UTC');
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        $valueValid = $theType->timezone($value)->orThrow();

        $configSet[EntrypointModule::OPT_PHP_DATE_TIMEZONE_DEFAULT] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        date_default_timezone_set($value->getName());
    }
}
