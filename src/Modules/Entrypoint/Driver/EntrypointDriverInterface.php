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


    public function setValue($value, array &$configCurrent) : void;

    public function useValue($value, array $configCurrent) : void;
}
