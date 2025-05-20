<?php

namespace Gzhegow\Lib\Modules\Fs;

use Gzhegow\Lib\Lib;


class FileSafeContext
{
    /**
     * @var array<int, resource>
     */
    protected $fhh = [];
    /**
     * @var array<int, resource>
     */
    protected $fhhToFrelease = [];
    /**
     * @var array<int, resource>
     */
    protected $fhhToFclose = [];

    /**
     * @var array<string, true>
     */
    protected $filesToUnlink = [];
    /**
     * @var array<string, true>
     */
    protected $filesToUnlinkIfEmpty = [];


    public function onFinally() : void
    {
        $isWindows = Lib::php()->is_windows();

        foreach ( $this->fhh as $fh ) {
            fflush($fh);
        }

        clearstatcache(true);

        if (! $isWindows) {
            foreach ( $this->filesToUnlink as $file => $bool ) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            foreach ( $this->filesToUnlinkIfEmpty as $file => $bool ) {
                if (is_file($file) && ! filesize($file)) {
                    unlink($file);
                }
            }
        }

        foreach ( $this->fhhToFrelease as $fh ) {
            if (is_resource($fh)) {
                flock($fh, LOCK_UN);
            }
        }

        foreach ( $this->fhhToFclose as $fh ) {
            if (is_resource($fh)) {
                fclose($fh);
            }
        }

        if ($isWindows) {
            foreach ( $this->filesToUnlink as $file => $bool ) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            foreach ( $this->filesToUnlinkIfEmpty as $file => $bool ) {
                if (is_file($file) && ! filesize($file)) {
                    unlink($file);
                }
            }
        }
    }


    /**
     * @param resource $fh
     *
     * @return static
     */
    public function onFinallyFrelease($fh)
    {
        $id = (int) $fh;

        $this->fhh[ $id ] = $fh;
        $this->fhhToFrelease[ $id ] = $fh;

        return $this;
    }

    /**
     * @param resource $fh
     *
     * @return static
     */
    public function onFinallyFclose($fh)
    {
        $id = (int) $fh;

        $this->fhh[ $id ] = $fh;
        $this->fhhToFclose[ $id ] = $fh;

        return $this;
    }


    /**
     * @param string $file
     *
     * @return static
     */
    public function onFinallyUnlink($file)
    {
        $this->filesToUnlink[ $file ] = true;

        return $this;
    }

    /**
     * @param string $file
     *
     * @return static
     */
    public function onFinallyUnlinkIfEmpty($file)
    {
        $this->filesToUnlinkIfEmpty[ $file ] = true;

        return $this;
    }
}
