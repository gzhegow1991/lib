<?php

namespace Gzhegow\Lib\Modules\Fs;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @method int umask(int $umask)
 *
 * @ method fopen()
 * @ method fclose()
 *
 * @ method flock()
 * @ method flock_pooling()
 * @ method frelease()
 *
 * @method bool feof(resource $resource)
 * @method int|false ftell(resource $resource)
 * @ method fseek()
 * @ method rewind()
 *
 * @method string|false fread(resource $resource, int $length)
 * @method int|false fwrite(resource $resource, string $data, int|null $length)
 * @ method fwrite_fflush()
 *
 * @method bool ftruncate(resource $resource, int $pos)
 *
 * @method string|false fgetc(resource $resource)
 * @method string|false fgets(resource $resource, int|null $length)
 *
 * @method int|false fputs(resource $resource, string $data, int $length)
 * @ method int|false fputs_fflush()
 *
 * @method int|false fpassthru(resource $stream)
 * @ method int|false fpassthru_fflush(resource $stream)
 *
 * @method bool fflush(resource $stream)
 * @method bool fsync(resource $stream)
 * @method bool fdatasync(resource $stream)
 *
 * @method string|false filetype(string $filename)
 *
 * @ method file_exists()
 * @ method is_exists()
 * @ method is_file()
 * @ method is_realfile()
 * @ method is_dir()
 * @ method is_realdir()
 *
 * @ method is_link()
 * @ method is_link_dir()
 * @ method is_link_file()
 * @ method is_link_target()
 *
 * @ method is_readable()
 * @ method is_writeable()
 * @ method is_executable()
 *
 * @ method clearstatcache()
 * @method array|false fstat(string $file)
 * @method array|false lstat(string $file)
 * @method array|false stat(resource $resource)
 *
 * @method string|false readlink(string $file)
 * @ method realpath()
 * @ method realpath_link()
 * @ method realpath_target()
 *
 * @method int|false fileatime(string $file)
 * @method int|false filectime(string $file)
 * @method int|false filegroup(string $file)
 * @method int|false fileinode(string $file)
 * @method int|false filemtime(string $file)
 * @method int|false fileowner(string $file)
 * @method int|false fileperms(string $file)
 *
 * @method int|false filesize(string $file)
 *
 * @ method chmod()
 * @ method chown()
 * @ method lchown()
 * @ method chgrp()
 * @ method lchgrp()
 *
 * @ method touch()
 * @ method copy()
 * @ method rename()
 *
 * @ method unlink()
 *
 * @ method rm()
 * @ method rmf()
 *
 * @ method mkdir()
 * @ method mkdirp()
 * @ method rmdir()
 * @ method rmdirf()
 *
 * @ method link()
 * @ method symlink()
 *
 * @ method file_read()
 * @ method file_read_flock()
 * @ method file_read_flock_pooling()
 *
 * @ method file_write()
 * @ method file_write_flock()
 * @ method file_write_flock_pooling()
 *
 * // @ method file()
 * @ method file_lines()
 * @ method file_lines_flock()
 * @ method file_lines_flock_pooling()
 *
 * // @ method readfile()
 * @ method file_echo()
 * @ method file_echo_flock()
 * @ method file_echo_flock_pooling()
 *
 * @ method file_get_contents()
 * @ method file_put_contents()
 *
 *
 * // @ method fgetss()
 *
 * // @ method fgetcsv()
 * // @ method fputcsv()
 *
 * // @ method basename()
 * // @ method dirname()
 *
 * // @ method linkinfo()
 * // @ method pathinfo()
 *
 * // @ method pclose()
 * // @ method popen()
 *
 * // @ method glob()
 *
 * // @ method parse_ini_file()
 * // @ method parse_ini_string()
 *
 * // @ method realpath_cache_get()
 * // @ method realpath_cache_size()
 *
 * // @ method set_file_buffer()
 *
 * // @ method tempnam()
 * // @ method tmpfile()
 *
 * // @ method is_uploaded_file()
 * // @ method move_uploaded_file()
 *
 * // @ method disk_free_space()
 * // @ method disk_total_space()
 * // @ method diskfreespace()
 */
class FileSafe
{
    /**
     * @return mixed
     */
    public function __call($name, $args)
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler($this->fnErrorHandler());

        $fn = $this->__callGetCallable($name);

        $result = call_user_func_array($fn, $args);

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }

    /**
     * @param string $name
     *
     * @return callable
     */
    protected function __callGetCallable(string $name)
    {
        /**
         * @var array<string, callable> $map
         */
        static $map;

        $map = $map ?? [
            'umask'                    => 'umask',
            //
            'fopen'                    => [ $this, 'fopen' ],
            'fclose'                   => [ $this, 'fclose' ],
            //
            'flock'                    => [ $this, 'flock' ],
            'flock_pooling'            => [ $this, 'flock_pooling' ],
            'frelease'                 => [ $this, 'frelease' ],
            //
            'fopen_flock'              => [ $this, 'fopen_flock' ],
            'fopen_flock_pooling'      => [ $this, 'fopen_flock_pooling' ],
            'frelease_fclose'          => [ $this, 'frelease_fclose' ],
            //
            'feof'                     => 'feof',
            'ftell'                    => 'ftell',
            'fseek'                    => [ $this, 'fseek' ],
            'rewind'                   => [ $this, 'rewind' ],
            //
            'fread'                    => [ $this, 'fread' ],
            'fwrite'                   => [ $this, 'fwrite' ],
            'fwrite_fflush'            => [ $this, 'fwrite_fflush' ],
            //
            'ftruncate'                => 'ftruncate',
            //
            'fgetc'                    => 'fgetc',
            'fgets'                    => 'fgets',
            //
            'fputs'                    => 'fputs',
            'fputs_fflush'             => [ $this, 'fputs_fflush' ],
            //
            'fpassthru'                => 'fpassthru',
            'fpassthru_fflush'         => [ $this, 'fpassthru_fflush' ],
            //
            'fflush'                   => 'fflush',
            'fsync'                    => 'fsync',
            'fdatasync'                => 'fdatasync',
            //
            'filetype'                 => 'filetype',
            //
            'file_exists'              => [ $this, 'file_exists' ],
            'is_exists'                => [ $this, 'is_exists' ],
            'is_file'                  => [ $this, 'is_file' ],
            'is_realfile'              => [ $this, 'is_realfile' ],
            'is_dir'                   => [ $this, 'is_dir' ],
            'is_realdir'               => [ $this, 'is_realdir' ],
            //
            'is_link'                  => [ $this, 'is_link' ],
            'is_link_dir'              => [ $this, 'is_link_dir' ],
            'is_link_file'             => [ $this, 'is_link_file' ],
            'is_link_target'           => [ $this, 'is_link_target' ],
            //
            'is_readable'              => [ $this, 'is_readable' ],
            'is_writable'              => [ $this, 'is_writable' ],
            'is_writeable'             => [ $this, 'is_writeable' ],
            'is_executable'            => [ $this, 'is_executable' ],
            //
            'clearstatcache'           => [ $this, 'clearstatcache' ],
            'fstat'                    => 'fstat',
            'lstat'                    => 'lstat',
            'stat'                     => 'stat',
            //
            'readlink'                 => 'readlink',
            'realpath'                 => [ $this, 'realpath' ],
            'realpath_link'            => [ $this, 'realpath_link' ],
            'realpath_target'          => [ $this, 'realpath_target' ],
            //
            'fileatime'                => 'fileatime',
            'filectime'                => 'filectime',
            'filegroup'                => 'filegroup',
            'fileinode'                => 'fileinode',
            'filemtime'                => 'filemtime',
            'fileowner'                => 'fileowner',
            'fileperms'                => 'fileperms',
            'filesize'                 => 'filesize',
            //
            'chmod'                    => [ $this, 'chmod' ],
            'chown'                    => [ $this, 'chown' ],
            'lchown'                   => [ $this, 'lchown' ],
            'chgrp'                    => [ $this, 'chgrp' ],
            'lchgrp'                   => [ $this, 'lchgrp' ],
            //
            'touch'                    => [ $this, 'touch' ],
            'copy'                     => [ $this, 'copy' ],
            'rename'                   => [ $this, 'rename' ],
            //
            'unlink'                   => [ $this, 'unlink' ],
            //
            'rm'                       => [ $this, 'rm' ],
            'rmf'                      => [ $this, 'rmf' ],
            //
            'mkdir'                    => [ $this, 'mkdir' ],
            'mkdirp'                   => [ $this, 'mkdirp' ],
            //
            'rmdir'                    => [ $this, 'rmdir' ],
            'rmdirf'                   => [ $this, 'rmdirf' ],
            //
            'link'                     => [ $this, 'link' ],
            'symlink'                  => [ $this, 'symlink' ],
            //
            'file_read'                => [ $this, 'file_read' ],
            'file_read_flock'          => [ $this, 'file_read_flock' ],
            'file_read_flock_pooling'  => [ $this, 'file_read_flock_pooling' ],
            //
            'file_write'               => [ $this, 'file_write' ],
            'file_write_flock'         => [ $this, 'file_write_flock' ],
            'file_write_flock_pooling' => [ $this, 'file_write_flock_pooling' ],
            //
            'file'                     => false,
            'file_lines'               => [ $this, 'file_lines' ],
            'file_lines_lock'          => [ $this, 'file_lines_flock' ],
            'file_lines_flock_pooling' => [ $this, 'file_lines_flock_pooling' ],
            //
            'readfile'                 => false,
            'file_echo'                => [ $this, 'file_echo' ],
            'file_echo_flock'          => [ $this, 'file_echo_flock' ],
            'file_echo_flock_pooling'  => [ $this, 'file_echo_flock_pooling' ],
            //
            'file_get_contents'        => [ $this, 'file_get_contents' ],
            'file_put_contents'        => [ $this, 'file_put_contents' ],
            //
            //
            'fgetss'                   => false,
            //
            'fgetcsv'                  => false,
            'fputcsv'                  => false,
            //
            'basename'                 => false,
            'dirname'                  => false,
            //
            'linkinfo'                 => false,
            'pathinfo'                 => false,
            //
            'pclose'                   => false,
            'popen'                    => false,
            //
            'glob'                     => false,
            //
            'parse_ini_file'           => false,
            'parse_ini_string'         => false,
            //
            'realpath_cache_get'       => false,
            'realpath_cache_size'      => false,
            //
            'set_file_buffer'          => false,
            //
            'tempnam'                  => false,
            'tmpfile'                  => false,
            //
            'is_uploaded_file'         => false,
            'move_uploaded_file'       => false,
            //
            'disk_free_space'          => false,
            'disk_total_space'         => false,
            'diskfreespace'            => false,
        ];

        $fn = $map[ $name ] ?: null;

        if (null === $fn) {
            throw new RuntimeException('Method is not exists: ' . $name);
        }

        return $fn;
    }


    /**
     * @return mixed
     */
    public function call(\Closure $closure)
    {
        $beforeErrorReporting = error_reporting(E_ALL | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED);
        $beforeErrorHandler = set_error_handler($this->fnErrorHandler());

        $ctx = new FileSafeContext();

        try {
            $result = call_user_func_array($closure, [ $ctx ]);
        }
        finally {
            $ctx->onFinally();
        }

        set_error_handler($beforeErrorHandler);
        error_reporting($beforeErrorReporting);

        return $result;
    }


    /**
     * @param string $file
     * @param string $modeOpen
     *
     * @return resource|false
     */
    protected function fopen(
        $file, $modeOpen,
        array $fopenArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen, false);

        $resource = call_user_func_array('fopen', $fopenArgs);

        if (false === $resource) {
            return false;
        }

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return closed-resource|bool
     */
    protected function fclose($resource)
    {
        $status = fclose($resource);

        if (false === $status) {
            return false;
        }

        return $resource;
    }


    /**
     * @param resource $resource
     * @param int      $modeLock
     *
     * @return resource|false
     */
    protected function flock(
        $resource, $modeLock,
        array $flockArgs = []
    )
    {
        array_unshift($flockArgs, $resource, $modeLock);

        $status = call_user_func_array('flock', $flockArgs);

        if (false === $status) {
            return false;
        }

        return $resource;
    }

    /**
     * @param int|null $tickUsleep
     * @param int|null $timeoutMs
     * @param resource $resource
     * @param int      $modeLock
     *
     * @return resource|false
     */
    protected function flock_pooling(
        $tickUsleep, $timeoutMs,
        $resource, $modeLock,
        array $flockArgs = []
    )
    {
        $fnTick = function (&$result) use (
            $resource, $modeLock,
            $flockArgs
        ) {
            if (! $this->flock(
                $resource, $modeLock | LOCK_NB,
                $flockArgs
            )) {
                return;
            }

            $result = [ $resource ];
        };

        $status = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        if (false === $status) {
            return false;
        }

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return resource|false
     */
    protected function frelease($resource)
    {
        $status = flock($resource, LOCK_UN);

        if (false === $status) {
            return false;
        }

        return $resource;
    }


    /**
     * @param string $file
     * @param string $modeOpen
     * @param int    $modeLock
     *
     * @return resource|false
     */
    protected function fopen_flock(
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen, false);

        $resource = call_user_func_array('fopen', $fopenArgs);

        if (false === $resource) {
            return false;
        }

        array_unshift($flockArgs, $resource, $modeLock);

        $status = call_user_func_array('flock', $flockArgs);

        if (false === $status) {
            return false;
        }

        return $resource;
    }

    /**
     * @param int|null $tickUsleep
     * @param int|null $timeoutMs
     * @param string   $file
     * @param string   $modeOpen
     * @param int      $modeLock
     *
     * @return resource|false
     */
    protected function fopen_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen, false);

        $resource = call_user_func_array('fopen', $fopenArgs);

        if (false === $resource) {
            return false;
        }

        $fnTick = function (&$result) use (
            $resource, $modeLock,
            $flockArgs
        ) {
            if (! $this->flock(
                $resource, $modeLock | LOCK_NB,
                $flockArgs
            )) {
                return;
            }

            $result = [ $resource ];
        };

        $resource = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        if (false === $resource) {
            return false;
        }

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return closed-resource|false
     */
    protected function frelease_fclose($resource)
    {
        $status = flock($resource, LOCK_UN);

        if (false === $status) {
            return false;
        }

        $status = fclose($resource);

        if (false === $status) {
            return false;
        }

        return $resource;
    }


    /**
     * @param resource $resource
     * @param int      $offset
     * @param int|null $whence
     *
     * @return int
     */
    protected function fseek($resource, int $offset, $whence = null)
    {
        $whence = $whence ?? SEEK_SET;

        $pos = fseek($resource, $offset, $whence);

        return $pos;
    }

    /**
     * @param resource $resource
     *
     * @return resource|false
     */
    protected function rewind($resource)
    {
        $status = rewind($resource);

        if (false === $status) {
            return false;
        }

        return $resource;
    }


    /**
     * @param resource $resource
     * @param string   $data
     * @param int|null $length
     *
     * @return int|false
     */
    protected function fwrite_fflush($resource, string $data, ?int $length = null)
    {
        $len = (null !== $length)
            ? fwrite($resource, $data, $length)
            : fwrite($resource, $data);

        if (false === $len) {
            return false;
        }

        fflush($resource);

        return $len;
    }


    /**
     * @param resource $resource
     * @param string   $data
     * @param int|null $length
     *
     * @return int|false
     */
    protected function fputs_fflush($resource, string $data, ?int $length = null)
    {
        $len = (null !== $length)
            ? fputs($resource, $data, $length)
            : fputs($resource, $data);

        if (false === $len) {
            return false;
        }

        fflush($resource);

        return $len;
    }


    /**
     * @param resource $resource
     *
     * @return int|false
     */
    protected function fpassthru_fflush($resource)
    {
        $size = fpassthru($resource);

        if (false === $size) {
            return false;
        }

        fflush(STDOUT);

        return $size;
    }


    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function file_exists($file)
    {
        $status = file_exists($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * > алиас для file_exists
     *
     * @param string $file
     *
     * @return string|false
     *
     * @see static::file_exists()
     */
    protected function is_exists($file)
    {
        $status = file_exists($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_file($file)
    {
        $status = is_file($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_realfile($file)
    {
        $status = is_file($file) && ! is_link($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string $directory
     *
     * @return string|false
     */
    protected function is_dir($directory)
    {
        $status = is_dir($directory);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($directory);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $directory
     *
     * @return string|false
     */
    protected function is_realdir($directory)
    {
        $status = is_dir($directory) && ! is_link($directory);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($directory);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_link($file)
    {
        $status = is_link($file);

        if (false === $status) {
            return false;
        }

        $realpath = $this->realpath_link($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_link_target($file)
    {
        $status = is_link($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_link_file($file)
    {
        $status = is_link($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (! is_file($realpath)) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $directory
     *
     * @return string|false
     */
    protected function is_link_dir($directory)
    {
        $status = is_link($directory);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($directory);

        if (! is_dir($realpath)) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_readable($file)
    {
        $status = is_readable($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_writable($file)
    {
        $status = is_writable($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_writeable($file)
    {
        $status = is_writeable($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function is_executable($file)
    {
        $status = is_executable($file);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param bool|null   $clear_realpath_cache
     * @param string|null $file
     *
     * @return true
     */
    protected function clearstatcache($clear_realpath_cache = null, $file = null) : bool
    {
        $clear_realpath_cache = $clear_realpath_cache ?? false;
        $file = $file ?? '';

        ('' !== $file)
            ? clearstatcache($clear_realpath_cache, $file)
            : clearstatcache($clear_realpath_cache);

        return true;
    }


    /**
     * @param string    $file
     * @param bool|null $returnTargetPath
     *
     * @return string|false
     */
    protected function realpath($file, $returnTargetPath = null)
    {
        $returnTargetPath = $returnTargetPath ?? Lib::fs()->static_realpath_return_target_path();
        $returnTargetPath = (bool) $returnTargetPath;

        if ($returnTargetPath) {
            $realpath = realpath($file);

        } else {
            $realpath = realpath(dirname($file));
        }

        if (false === $realpath) {
            return false;
        }

        if (! $returnTargetPath) {
            $realpath .= DIRECTORY_SEPARATOR . basename($file);
        }

        return $realpath;
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function realpath_target($file)
    {
        return realpath($file);
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function realpath_link($file)
    {
        $realpath = realpath(dirname($file));

        if (false === $realpath) {
            return false;
        }

        $realpath .= DIRECTORY_SEPARATOR . basename($file);

        return $realpath;
    }


    /**
     * @param string $file
     * @param int    $permissions
     *
     * @return string|false
     */
    protected function chmod($file, $permissions)
    {
        $status = chmod($file, $permissions);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string     $file
     * @param string|int $user
     *
     * @return string|false
     */
    protected function chown($file, $user)
    {
        $status = chown($file, $user);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string     $file
     * @param string|int $user
     *
     * @return string|false
     */
    protected function lchown($file, $user)
    {
        $status = lchown($file, $user);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string     $file
     * @param string|int $group
     *
     * @return string|false
     */
    protected function chgrp($file, $group)
    {
        $status = chgrp($file, $group);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string     $file
     * @param string|int $group
     *
     * @return string|false
     */
    protected function lchgrp($file, $group)
    {
        $status = lchgrp($file, $group);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string   $file
     * @param int|null $mtime
     * @param int|null $atime
     *
     * @return string|false
     */
    protected function touch($file, $mtime = null, $atime = null)
    {
        $args = [];
        if (null !== $mtime) $args[] = $mtime;
        if (null !== $atime) $args[] = $atime;

        $status = touch($file, ...$args);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string|null $from
     * @param string|null $to
     *
     * @return string|false
     */
    protected function copy($from, $to, $context = null)
    {
        $status = (null !== $context)
            ? copy($from, $to, $context)
            : copy($from, $to);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($to);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string|null $from
     * @param string|null $to
     *
     * @return string|false
     */
    protected function rename($from, $to, $context = null)
    {
        $status = (null !== $context)
            ? rename($from, $to, $context)
            : rename($from, $to);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($to);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * > выполняет unlink(), затем clearstatcache()
     * > в PHP из коробки она выполняет её для всех ресурсов, кроме расположенных вне текущей машины
     *
     * @param string $file
     *
     * @return string|false
     */
    protected function unlink($file, $context = null)
    {
        $status = (null !== $context)
            ? unlink($file, $context)
            : unlink($file);

        if (false === $status) {
            return false;
        }

        clearstatcache(true, $file);

        return $file;
    }


    /**
     * @param string $file
     *
     * @return string|false
     */
    protected function rm($file, $context = null)
    {
        $status = is_file($file) && ! is_link($file);

        if (false === $status) {
            return false;
        }

        $status = unlink($file, $context);

        if (false === $status) {
            return false;
        }

        clearstatcache(true, $file);

        return $file;
    }

    /**
     * > удаляет в том числе символическую ссылку на файл
     *
     * @param string $file
     *
     * @return string|false
     */
    protected function rmf($file, $context = null)
    {
        if (! is_file($file)) {
            return $file;
        }

        $status = unlink($file, $context);

        if (false === $status) {
            return false;
        }

        clearstatcache(true, $file);

        return $file;
    }


    /**
     * @param string    $directory
     * @param int|null  $permissions
     * @param bool|null $recursive
     *
     * @return string|false
     */
    protected function mkdir($directory, $permissions = null, $recursive = null, $context = null)
    {
        $permissions = $permissions ?? Lib::fs()->static_dir_chmod();
        $recursive = $recursive ?? false;

        $status = (null !== $context)
            ? mkdir($directory, $permissions, $recursive, $context)
            : mkdir($directory, $permissions, $recursive);

        if (false === $status) {
            return false;
        }

        $realpath = realpath($directory);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string    $directory
     * @param int|null  $permissions
     * @param bool|null $recursive
     *
     * @return string|false
     */
    protected function mkdirp($directory, $permissions = null, $recursive = null, $context = null)
    {
        if (! is_dir($directory)) {
            $permissions = $permissions ?? Lib::fs()->static_dir_chmod();
            $recursive = $recursive ?? true;

            $status = (null !== $context)
                ? mkdir($directory, $permissions, $recursive, $context)
                : mkdir($directory, $permissions, $recursive);

            if (false === $status) {
                return false;
            }
        }

        $realpath = realpath($directory);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string $directory
     *
     * @return string|false
     */
    protected function rmdir($directory, $context = null)
    {
        $status = is_dir($directory) && ! is_link($directory);

        if (false === $status) {
            return false;
        }

        $status = (null !== $context)
            ? rmdir($directory, $context)
            : rmdir($directory);

        if (false === $status) {
            return false;
        }

        clearstatcache(true, $directory);

        return $directory;
    }

    /**
     * > удаляет в том числе символическую ссылку на папку
     *
     * @param string $directory
     *
     * @return string|false
     */
    protected function rmdirf($directory, $context = null)
    {
        if (! is_dir($directory)) {
            return $directory;
        }

        if (is_link($directory)) {
            $status = unlink($directory, $context);

        } else {
            $status = rmdir($directory, $context);
        }

        if (false === $status) {
            return false;
        }

        clearstatcache(true, $directory);

        return $directory;
    }


    /**
     * @param string $target
     * @param string $link
     *
     * @return string|false
     */
    protected function link($target, $link)
    {
        $status = link($target, $link);

        if (false === $status) {
            return false;
        }

        $realpath = $this->realpath_link($link);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }

    /**
     * @param string $target
     * @param string $link
     *
     * @return string|false
     */
    protected function symlink($target, $link)
    {
        $status = symlink($target, $link);

        if (false === $status) {
            return false;
        }

        $realpath = $this->realpath_link($link);

        if (false === $realpath) {
            return false;
        }

        return $realpath;
    }


    /**
     * @param string $file
     * @param string $modeOpen
     *
     * @return string|false
     */
    protected function file_read(
        $file, $modeOpen,
        array $fopenArgs = [],
        array $streamGetContentsArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen);

        $fh = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

        if (false === $fh) {
            return false;
        }

        $content = stream_get_contents($fh, ...$streamGetContentsArgs);

        if (false === $content) {
            return false;
        }

        fclose($fh);

        return $content;
    }

    /**
     * @param string $file
     * @param string $modeOpen
     * @param int    $modeLock
     *
     * @return string|false
     */
    protected function file_read_flock(
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = [],
        array $streamGetContentsArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen);

        $resource = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

        if (false === $resource) {
            return false;
        }

        array_unshift($flockArgs, $resource, $modeLock);

        $resource = call_user_func_array([ $this, 'flock' ], $flockArgs);

        if (false === $resource) {
            return false;
        }

        $content = stream_get_contents($resource, ...$streamGetContentsArgs);

        if (false === $content) {
            return false;
        }

        flock($resource, LOCK_UN);
        fclose($resource);

        return $content;
    }

    /**
     * @param int|null $tickUsleep
     * @param int|null $timeoutMs
     * @param string   $file
     * @param string   $modeOpen
     * @param int      $modeLock
     *
     * @return string|false
     */
    protected function file_read_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = [],
        array $streamGetContentsArgs = []
    )
    {
        $fnTick = function (&$result) use (
            $file, $modeOpen, $modeLock,
            $fopenArgs,
            $flockArgs,
            $streamGetContentsArgs
        ) {
            $content = $this->file_read_flock(
                $file, $modeOpen, $modeLock | LOCK_NB,
                $fopenArgs,
                $flockArgs,
                $streamGetContentsArgs,
            );

            if (false === $content) {
                return;
            }

            $result = [ $content ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    /**
     * @param string                   $file
     * @param string                   $modeOpen
     * @param string|string[]|resource $data
     * @param bool|null                $isAppend
     *
     * @return int|false
     */
    protected function file_write(
        $file, $modeOpen,
        $data, $isAppend = null,
        array $fopenArgs = []
    )
    {
        $isAppend = $isAppend ?? false;

        array_unshift($fopenArgs, $file, $modeOpen);

        $resource = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

        if (false === $resource) {
            return false;
        }

        if ($isAppend) {
            fseek($resource, 0, SEEK_END);

        } else {
            rewind($resource);
            ftruncate($resource, 0);
        }

        if (is_resource($data)) {
            $len = stream_copy_to_stream($data, $resource);

            if (false === $len) {
                return false;
            }

        } elseif (is_array($data)) {
            $theType = Lib::type();

            $len = 0;
            foreach ( $data as $i => $line ) {
                if (! $theType->string($lineString, $line)) {
                    throw new LogicException(
                        [ 'The `data` should be array of strings', $i, $line ]
                    );
                }

                $lenLine = fwrite($resource, $line);

                if (false === $lenLine) {
                    return false;
                }

                $len += $lenLine;
            }

        } elseif (is_string($data)) {
            $len = fwrite($resource, $data);

            if (false === $len) {
                return false;
            }

        } else {
            throw new LogicException(
                [ 'The `data` should be string, array of strings or resource', $data ]
            );
        }

        fclose($resource);

        clearstatcache(true, $file);

        return $len;
    }

    /**
     * @param string                   $file
     * @param string                   $modeOpen
     * @param int                      $modeLock
     * @param string|string[]|resource $data
     *
     * @return int|false
     */
    protected function file_write_flock(
        $file, $modeOpen, $modeLock,
        $data,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen);

        $resource = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

        if (false === $resource) {
            return false;
        }

        array_unshift($flockArgs, $resource, $modeLock);

        $resource = call_user_func_array([ $this, 'flock' ], $flockArgs);

        if (false === $resource) {
            return false;
        }

        if (is_resource($data)) {
            $len = stream_copy_to_stream($data, $resource);

            if (false === $len) {
                return false;
            }

        } elseif (is_array($data)) {
            $theType = Lib::type();

            $len = 0;
            foreach ( $data as $i => $line ) {
                if (! $theType->string($lineString, $line)) {
                    throw new LogicException(
                        [ 'The `data` should be array of strings', $i, $line ]
                    );
                }

                $lenLine = fwrite($resource, $line);

                if (false === $lenLine) {
                    return false;
                }

                $len += $lenLine;
            }

        } elseif (is_string($data)) {
            $len = fwrite($resource, $data);

            if (false === $len) {
                return false;
            }

        } else {
            throw new LogicException(
                [ 'The `data` should be string, array of strings or resource', $data ]
            );
        }

        flock($resource, LOCK_UN);
        fclose($resource);

        clearstatcache(true, $file);

        return $len;
    }

    /**
     * @param int|null                 $tickUsleep
     * @param int|null                 $timeoutMs
     * @param string                   $file
     * @param string                   $modeOpen
     * @param int                      $modeLock
     * @param string|string[]|resource $data
     *
     * @return int|false
     */
    protected function file_write_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        $data,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $fnTick = function (&$result) use (
            $file, $modeOpen, $modeLock,
            $data,
            $fopenArgs,
            $flockArgs
        ) {
            $len = $this->file_write_flock(
                $file, $modeOpen, $modeLock | LOCK_NB,
                $data,
                $fopenArgs,
                $flockArgs
            );

            if (false === $len) {
                return;
            }

            $result = [ $len ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    // > функция переименована в file_lines()
    // /**
    //  * @param string   $file
    //  * @param int|null $flags
    //  *
    //  * @return string[]|false
    //  */
    // protected function file($file, $flags = null, $context = null)
    // {
    //     $flags = $flags ?? 0;
    //     $flags &= ~FILE_USE_INCLUDE_PATH;
    //
    //     $lines = (null !== $context)
    //         ? file($file, $flags, $context)
    //         : file($file, $flags);
    //
    //     return $lines;
    // }

    /**
     * @param string   $file
     * @param int|null $flags
     *
     * @return string[]|false
     */
    protected function file_lines(
        $file, $flags = null,
        array $fopenArgs = []
    )
    {
        $flags = $flags ?? 0;
        $flags &= ~FILE_USE_INCLUDE_PATH;

        $content = $this->file_read(
            $file, 'r',
            $fopenArgs
        );

        if (false === $content) {
            return false;
        }

        $isIgnoreNewLines = ($flags & FILE_IGNORE_NEW_LINES);
        $isSkipEmptyLines = ($flags & FILE_SKIP_EMPTY_LINES);

        $regex = '/(\R)/';

        $lines = [];

        if ($isIgnoreNewLines) {
            $parts = preg_split($regex, $content);

            if ($parts === [ '' ] && $isSkipEmptyLines) {
                return [];
            }

            $cnt = count($parts);

            for ( $i = 0; $i < $cnt; $i++ ) {
                $line = $parts[ $i ];

                if (('' === $line) && $isSkipEmptyLines) {
                    continue;
                }

                $lines[] = $line;
            }

        } else {
            $parts = preg_split($regex, $content, -1, PREG_SPLIT_DELIM_CAPTURE);

            if ($parts === [ '' ] && $isSkipEmptyLines) {
                return [];
            }

            $cnt = count($parts);

            for ( $i = 0; $i < $cnt; $i += 2 ) {
                $line = $parts[ $i ];

                if (('' === $line) && $isSkipEmptyLines) {
                    continue;
                }

                $eol = $parts[ $i + 1 ] ?? '';

                $lines[] = $line . $eol;
            }
        }

        return $lines;
    }

    /**
     * @param string   $file
     * @param int      $modeLock
     * @param int|null $flags
     *
     * @return string[]|false
     */
    protected function file_lines_flock(
        $file, $modeLock, $flags = null,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $flags = $flags ?? 0;
        $flags &= ~FILE_USE_INCLUDE_PATH;

        $content = $this->file_read_flock(
            $file, 'r', $modeLock,
            $fopenArgs,
            $flockArgs
        );

        if (false === $content) {
            return false;
        }

        $isIgnoreNewLines = ($flags & FILE_IGNORE_NEW_LINES);
        $isSkipEmptyLines = ($flags & FILE_SKIP_EMPTY_LINES);

        $regex = '/(\R)/';

        $lines = [];

        if ($isIgnoreNewLines) {
            $parts = preg_split($regex, $content);

            if ($parts === [ '' ] && $isSkipEmptyLines) {
                return [];
            }

            $cnt = count($parts);

            for ( $i = 0; $i < $cnt; $i++ ) {
                $line = $parts[ $i ];

                if (('' === $line) && $isSkipEmptyLines) {
                    continue;
                }

                $lines[] = $line;
            }

        } else {
            $parts = preg_split($regex, $content, -1, PREG_SPLIT_DELIM_CAPTURE);

            if ($parts === [ '' ] && $isSkipEmptyLines) {
                return [];
            }

            $cnt = count($parts);

            for ( $i = 0; $i < $cnt; $i += 2 ) {
                $line = $parts[ $i ];

                if (('' === $line) && $isSkipEmptyLines) {
                    continue;
                }

                $eol = $parts[ $i + 1 ] ?? '';

                $lines[] = $line . $eol;
            }
        }

        return $lines;
    }

    /**
     * @param int|null $tickUsleep
     * @param int|null $timeoutMs
     * @param string   $file
     * @param int|null $flags
     *
     * @return string[]|false
     */
    protected function file_lines_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeLock, $flags = null,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $fnTick = function (&$result) use (
            $file, $modeLock, $flags,
            $fopenArgs,
            $flockArgs
        ) {
            $content = $this->file_lines_flock(
                $file, $modeLock | LOCK_NB, $flags,
                $fopenArgs,
                $flockArgs,
            );

            if (false === $content) {
                return;
            }

            $result = [ $content ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    // > функция переименована в file_echo()
    // /**
    //  * @param string $file
    //  *
    //  * @return int|false
    //  */
    // protected function readfile($file, $context = null)
    // {
    //     $size = (null !== $context)
    //         ? readfile($file, false, $context)
    //         : readfile($file);
    //
    //     return $size;
    // }

    /**
     * @param string $file
     *
     * @return int|false
     */
    protected function file_echo(
        $file,
        array $fopenArgs = []
    )
    {
        array_unshift($fopenArgs, $file, 'r');

        $fh = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

        if (false === $fh) {
            return false;
        }

        $size = fpassthru($fh);

        if (false === $size) {
            return false;
        }

        fclose($fh);

        return $size;
    }

    /**
     * @param string $file
     * @param int    $modeLock
     *
     * @return int|false
     */
    protected function file_echo_flock(
        $file, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        array_unshift($fopenArgs, $file, 'r');

        $resource = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

        if (false === $resource) {
            return false;
        }

        array_unshift($flockArgs, $resource, $modeLock);

        $resource = call_user_func_array([ $this, 'flock' ], $flockArgs);

        if (false === $resource) {
            return false;
        }

        $size = fpassthru($resource);

        if (false === $size) {
            return false;
        }

        fclose($resource);

        return $size;
    }

    /**
     * @param int|null $tickUsleep
     * @param int|null $timeoutMs
     * @param string   $file
     * @param int      $modeLock
     *
     * @return int|false
     */
    protected function file_echo_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $fnTick = function (&$result) use (
            $file, $modeLock,
            $fopenArgs,
            $flockArgs
        ) {
            $size = $this->file_echo_flock(
                $file, $modeLock | LOCK_NB,
                $fopenArgs,
                $flockArgs,
            );

            if (false === $size) {
                return;
            }

            $result = [ $size ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    /**
     * @param string   $file
     * @param int|null $offset
     * @param int|null $length
     *
     * @return string|false
     */
    protected function file_get_contents($file, $offset = null, $length = null, $context = null)
    {
        $args = [];
        if (null !== $length) array_unshift($args, $length);
        if (null !== $offset) array_unshift($args, $offset);
        if (null !== $context) array_unshift($args, $context);
        array_unshift($args, $file, false);

        $content = call_user_func_array('file_get_contents', $args);

        if (false === $content) {
            return false;
        }

        return $content;
    }

    /**
     * > выполняет file_put_contents(), затем clearstatcache(true, file)
     * > из коробки в PHP это единственная функция блочной записи, которая не обновляет после себя кэш stat файла
     *
     * @param string                   $file
     * @param string|string[]|resource $data
     * @param int|null                 $flags
     *
     * @return string|false
     */
    protected function file_put_contents(
        $file, $data, $flags = null,
        array $filePutContentsArgs = [],
        ?array $mkdirpArgs = [],
        ?array $chmodArgs = null
    )
    {
        $hasMkdirp = (null !== $mkdirpArgs);
        $hasChmod = (null !== $chmodArgs);

        $flags = $flags ?? 0;
        $flags &= ~FILE_USE_INCLUDE_PATH;

        if ($hasMkdirp) {
            $dirFile = dirname($file);

            if ('.' !== $dirFile) {
                array_unshift($mkdirpArgs, dirname($file));

                $realpath = call_user_func_array([ $this, 'mkdirp' ], $mkdirpArgs);

                if (false === $realpath) {
                    return false;
                }
            }
        }

        array_unshift($filePutContentsArgs, $file, $data, $flags);

        $len = call_user_func_array('file_put_contents', $filePutContentsArgs);

        if (false === $len) {
            return false;
        }

        $realpath = realpath($file);

        if (false === $realpath) {
            return false;
        }

        if ($hasChmod) {
            array_unshift($chmodArgs, $realpath);

            $status = call_user_func_array('chmod', $chmodArgs);

            if (false === $status) {
                return false;
            }
        }

        clearstatcache(true, $realpath);

        return $realpath;
    }


    protected function fnErrorHandler() : \Closure
    {
        /** @var \Closure $fn */
        static $fn;

        return $fn = $fn ?? function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, -1, $errno, $errfile, $errline);
        };
    }
}
