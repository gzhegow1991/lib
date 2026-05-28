<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\EntrypointModule;


class EntrypointPhpIntlLocaleDriver extends AbstractEntrypointDriver
{
    public function getInitial()
    {
        return extension_loaded('intl')
            ? \Locale::getDefault()
            : null;
    }

    public function getRecommended()
    {
        return 'en_US';
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        $theType = Lib::type();

        if ( null === $value ) {
            $valueValid = null;

        } else {
            $valueValid = $theType->locale($value)->orThrow();
        }

        $configSet[EntrypointModule::OPT_PHP_INTL_LOCALE_DEFAULT] = $valueValid;
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        if ( ! extension_loaded('intl') ) {
            return;
        }

        $value = $value ?? $configInitial[EntrypointModule::OPT_PHP_INTL_LOCALE_DEFAULT];

        \Locale::setDefault($value);
    }
}
