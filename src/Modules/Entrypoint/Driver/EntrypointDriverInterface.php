<?php

namespace Gzhegow\Lib\Modules\Entrypoint\Driver;


interface EntrypointDriverInterface
{
    /**
     * @return mixed
     */
    public function getInitial();

    /**
     * @return mixed
     */
    public function getRecommended();


    public function setValue($value, array &$configSet, array $configInitial) : void;

    public function useValue($value, array $configCurrent, array $configInitial) : void;
}
