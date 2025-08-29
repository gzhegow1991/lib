<?php

namespace Gzhegow\Lib\Modules\Fs\FileSafe;

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
     * @var array<string, bool>
     */
    protected $files = [];
    /**
     * @var array<string, true>
     */
    protected $filesToUnlink = [];


    public function handleOnFinally() : void
    {
        $thePhp = Lib::php();

        [ $fhh, $this->fhh ] = [ $this->fhh, [] ];
        [ $fhhToFrelease, $this->fhhToFrelease ] = [ $this->fhhToFrelease, [] ];
        [ $fhhToFclose, $this->fhhToFclose ] = [ $this->fhhToFclose, [] ];

        [ , $this->files ] = [ $this->files, [] ];
        [ $filesToUnlink, $this->filesToUnlink ] = [ $this->filesToUnlink, [] ];

        $isWindows = $thePhp->is_windows();

        $isResource = [];

        foreach ( $fhh as $i => $fh ) {
            $isResource[$i] = is_resource($fh);

            if ( $isResource[$i] ) {
                fflush($fh);
            }
        }

        if ( ! $isWindows ) {
            foreach ( $filesToUnlink as $file => $bool ) {
                if ( file_exists($file) ) {
                    unlink($file);
                    clearstatcache(true, $file);
                }
            }
        }

        foreach ( $fhhToFrelease as $i => $fh ) {
            if ( $isResource[$i] ) {
                flock($fh, LOCK_UN);
            }
        }

        foreach ( $fhhToFclose as $i => $fh ) {
            if ( $isResource[$i] ) {
                fclose($fh);
            }
        }

        if ( $isWindows ) {
            foreach ( $filesToUnlink as $file => $bool ) {
                if ( file_exists($file) ) {
                    unlink($file);
                    clearstatcache(true, $file);
                }
            }
        }

        clearstatcache();
    }


    /**
     * @param resource $fh
     *
     * @return static
     */
    public function onFinallyFrelease($fh)
    {
        $id = (int) $fh;

        $this->fhh[$id] = $fh;
        $this->fhhToFrelease[$id] = $fh;

        return $this;
    }

    /**
     * @param resource $fh
     *
     * @return static
     */
    public function offFinallyFrelease($fh)
    {
        $id = (int) $fh;

        unset($this->fhhToFrelease[$id]);

        if ( ! isset($this->fhhToFclose[$id]) ) {
            unset($this->fhh[$id]);
        }

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

        $this->fhh[$id] = $fh;
        $this->fhhToFclose[$id] = $fh;

        return $this;
    }

    /**
     * @param resource $fh
     *
     * @return static
     */
    public function offFinallyFclose($fh)
    {
        $id = (int) $fh;

        unset($this->fhhToFclose[$id]);

        if ( ! isset($this->fhhToFrelease[$id]) ) {
            unset($this->fhh[$id]);
        }

        return $this;
    }


    /**
     * @param string $file
     *
     * @return static
     */
    public function onFinallyUnlink($file)
    {
        $this->files[$file] = true;
        $this->filesToUnlink[$file] = true;

        return $this;
    }

    /**
     * @param string $file
     *
     * @return static
     */
    public function offFinallyUnlink($file)
    {
        unset($this->files[$file]);
        unset($this->filesToUnlink[$file]);

        return $this;
    }
}
