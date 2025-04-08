<?php

namespace Gzhegow\Lib\Modules\Arr;

use Gzhegow\Lib\Modules\Php\Interfaces\ToArrayInterface;


class ArrPath implements
    ToArrayInterface
{
    /**
     * @var array
     */
    protected $path;


    public function __construct(array $path)
    {
        $this->path = $path;
    }


    public function getPath() : array
    {
        return $this->path;
    }


    public function toArray(array $options = []) : array
    {
        return $this->path;
    }
}
