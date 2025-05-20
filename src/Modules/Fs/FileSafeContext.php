<?php

namespace Gzhegow\Lib\Modules\Fs;

class FileSafeContext
{
    /**
     * @var array<int, resource>
     */
    protected $fhhToFclose = [];
    /**
     * @var array<int, resource>
     */
    protected $fhhToFrelease = [];
    /**
     * @var array<string, true>
     */
    protected $filesToUnlink = [];


    /**
     * @return array<int, resource>
     */
    public function getFhhToFclose() : array
    {
        return $this->fhhToFclose;
    }

    /**
     * @param resource $fh
     *
     * @return static
     */
    public function finallyFclose($fh)
    {
        $this->fhhToFclose[ (int) $fh ] = $fh;

        return $this;
    }


    /**
     * @return array<int, resource>
     */
    public function getFhhToFrelease() : array
    {
        return $this->fhhToFrelease;
    }

    /**
     * @param resource $fh
     *
     * @return static
     */
    public function finallyFrelease($fh)
    {
        $this->fhhToFrelease[ (int) $fh ] = $fh;

        return $this;
    }


    /**
     * @return array<string, true>
     */
    public function getFilesToUnlink() : array
    {
        return $this->filesToUnlink;
    }

    /**
     * @return static
     */
    public function finallyUnlink(string $file)
    {
        $this->filesToUnlink[ $file ] = true;

        return $this;
    }
}
