<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;


abstract class AbstractEntrypointDriver implements EntrypointDriverInterface
{
    /**
     * @return mixed
     */
    public function getInitial()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getRecommended()
    {
        return null;
    }


    public function setValue($value, array &$configSet, array $configInitial) : void
    {
        //
    }

    public function useValue($value, array $configCurrent, array $configInitial) : void
    {
        //
    }
}
