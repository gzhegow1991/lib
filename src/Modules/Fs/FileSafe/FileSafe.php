<?php

namespace Gzhegow\Lib\Modules\Fs\FileSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class FileSafe
{
    /**
     * @var FileSafeContext
     */
    protected $context;


    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new RuntimeException(
                'Missing PHP extension: fileinfo'
            );
        }

        $this->context = new FileSafeContext();
    }


    public function setContext(?FileSafeContext $context) : ?FileSafeContext
    {
        $last = $this->context;

        $this->context = $context;

        return $last;
    }


    /**
     * @param string $file
     * @param string $modeOpen
     *
     * @return resource|false
     */
    public function fopen(
        $file, $modeOpen,
        array $fopenArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen, false);

        $resource = call_user_func_array('fopen', $fopenArgs);

        if (false === $resource) {
            return false;
        }

        $this->context->onFinallyFclose($resource);

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return closed-resource|bool
     */
    public function fclose($resource)
    {
        $status = fclose($resource);

        if (false === $status) {
            return false;
        }

        $this->context->offFinallyFclose($resource);

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return closed-resource|bool
     */
    public function fclosef($resource)
    {
        if (! Lib::type()->resource($var, $resource)) {
            throw new LogicException(
                [ 'The `resource` should be resource', $resource ]
            );
        }

        if (is_resource($resource)) {
            $status = fclose($resource);

            if (false === $status) {
                return false;
            }
        }

        $this->context->offFinallyFclose($resource);

        return $resource;
    }


    /**
     * @param resource $resource
     * @param int      $modeLock
     *
     * @return resource|false
     */
    public function flock(
        $resource, $modeLock,
        array $flockArgs = []
    )
    {
        array_unshift($flockArgs, $resource, $modeLock);

        $status = call_user_func_array('flock', $flockArgs);

        if (false === $status) {
            return false;
        }

        $this->context->onFinallyFrelease($resource);

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
    public function flock_pooling(
        $tickUsleep, $timeoutMs,
        $resource, $modeLock,
        array $flockArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        $fnTick = function (&$refResult) use (
            $resource, $modeLock,
            $flockArgs
        ) {
            array_unshift($flockArgs, $resource, $modeLock);

            $status = call_user_func_array('flock', $flockArgs);

            if (false === $status) {
                return;
            }

            $this->context->onFinallyFrelease($resource);

            $refResult = [ $resource ];
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
    public function frelease($resource)
    {
        $status = flock($resource, LOCK_UN);

        if (false === $status) {
            return false;
        }

        $this->context->offFinallyFrelease($resource);

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return resource|false
     */
    public function freleasef($resource)
    {
        if (! Lib::type()->resource($var, $resource)) {
            throw new LogicException(
                [ 'The `resource` should be resource', $resource ]
            );
        }

        if (is_resource($resource)) {
            $status = flock($resource, LOCK_UN);

            if (false === $status) {
                return false;
            }
        }

        $this->context->offFinallyFrelease($resource);

        return $resource;
    }


    /**
     * @param string $file
     * @param string $modeOpen
     * @param int    $modeLock
     *
     * @return resource|false
     */
    public function fopen_flock(
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

        $this->context->onFinallyFclose($resource);

        array_unshift($flockArgs, $resource, $modeLock);

        $status = call_user_func_array('flock', $flockArgs);

        if (false === $status) {
            return false;
        }

        $this->context->onFinallyFrelease($resource);

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
    public function fopen_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        array_unshift($fopenArgs, $file, $modeOpen, false);

        $resource = call_user_func_array('fopen', $fopenArgs);

        if (false === $resource) {
            return false;
        }

        $this->context->onFinallyFclose($resource);

        $fnTick = function (&$refResult) use (
            $resource, $modeLock,
            $flockArgs
        ) {
            array_unshift($flockArgs, $resource, $modeLock);

            $status = call_user_func_array('flock', $flockArgs);

            if (false === $status) {
                return;
            }

            $this->context->onFinallyFrelease($resource);

            $refResult = [ $resource ];
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
    public function frelease_fclose($resource)
    {
        $status = flock($resource, LOCK_UN);

        if (false === $status) {
            return false;
        }

        $this->context->offFinallyFrelease($resource);

        $status = fclose($resource);

        if (false === $status) {
            return false;
        }

        $this->context->offFinallyFclose($resource);

        return $resource;
    }

    /**
     * @param resource $resource
     *
     * @return closed-resource|false
     */
    public function freleasef_fclosef($resource)
    {
        if (! Lib::type()->resource($var, $resource)) {
            throw new LogicException(
                [ 'The `resource` should be resource', $resource ]
            );
        }

        $isResource = is_resource($resource);

        if ($isResource) {
            $status = flock($resource, LOCK_UN);

            if (false === $status) {
                return false;
            }
        }

        $this->context->offFinallyFrelease($resource);

        if ($isResource) {
            $status = fclose($resource);

            if (false === $status) {
                return false;
            }
        }

        $this->context->offFinallyFclose($resource);

        return $resource;
    }


    /**
     * @param string $file
     * @param string $modeOpen
     * @param int    $modeLock
     *
     * @return resource|false
     */
    public function fopen_flock_tmpfile(
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

        $this->context->onFinallyFclose($resource);
        $this->context->onFinallyUnlink($file);

        array_unshift($flockArgs, $resource, $modeLock);

        $status = call_user_func_array('flock', $flockArgs);

        if (false === $status) {
            return false;
        }

        $this->context->onFinallyFrelease($resource);

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
    public function fopen_flock_tmpfile_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        array_unshift($fopenArgs, $file, $modeOpen, false);

        $resource = call_user_func_array('fopen', $fopenArgs);

        if (false === $resource) {
            return false;
        }

        $this->context->onFinallyUnlink($file);
        $this->context->onFinallyFclose($resource);

        $fnTick = function (&$refResult) use (
            $resource, $modeLock,
            $flockArgs
        ) {
            array_unshift($flockArgs, $resource, $modeLock);

            $status = call_user_func_array('flock', $flockArgs);

            if (false === $status) {
                return;
            }

            $this->context->onFinallyFrelease($resource);

            $refResult = [ $resource ];
        };

        $resource = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        if (false === $resource) {
            return false;
        }

        return $resource;
    }

    /**
     * @param resource $resource
     * @param string   $file
     *
     * @return closed-resource|false
     */
    public function frelease_fclose_unlink($resource, $file)
    {
        $isWindows = Lib::php()->is_windows();

        if (! $isWindows) {
            $status = unlink($file);

            if (false === $status) {
                return false;
            }

            $this->context->offFinallyUnlink($file);
        }

        $status = flock($resource, LOCK_UN);

        if (false === $status) {
            return false;
        }

        $this->context->offFinallyFrelease($resource);

        $status = fclose($resource);

        if (false === $status) {
            return false;
        }

        $this->context->offFinallyFclose($resource);

        if ($isWindows) {
            $status = unlink($file);

            if (false === $status) {
                return false;
            }

            $this->context->offFinallyUnlink($file);
        }

        return $resource;
    }

    /**
     * @param resource $resource
     * @param string   $file
     *
     * @return closed-resource|false
     */
    public function freleasef_fclosef_unlinkf($resource, $file)
    {
        if (! Lib::type()->resource($var, $resource)) {
            throw new LogicException(
                [ 'The `resource` should be resource', $resource ]
            );
        }

        $isWindows = Lib::php()->is_windows();

        $isFile = is_file($file);

        if (! $isWindows) {
            if ($isFile) {
                $status = unlink($file);

                if (false === $status) {
                    return false;
                }
            }

            $this->context->offFinallyUnlink($file);
        }

        $isResource = is_resource($resource);

        if ($isResource) {
            $status = flock($resource, LOCK_UN);

            if (false === $status) {
                return false;
            }
        }

        $this->context->offFinallyFrelease($resource);

        if ($isResource) {
            $status = fclose($resource);

            if (false === $status) {
                return false;
            }
        }

        $this->context->offFinallyFclose($resource);

        if ($isWindows) {
            if ($isFile) {
                $status = unlink($file);

                if (false === $status) {
                    return false;
                }
            }

            $this->context->offFinallyUnlink($file);
        }

        return $resource;
    }


    /**
     * @param resource $resource
     * @param int      $offset
     * @param int|null $whence
     *
     * @return int|false
     */
    public function fseek($resource, int $offset, $whence = null)
    {
        $whence = $whence ?? SEEK_SET;

        $statusInt = fseek($resource, $offset, $whence);

        if (-1 === $statusInt) {
            return false;
        }

        $pos = ftell($resource);

        if (false === $pos) {
            return false;
        }

        return $pos;
    }

    /**
     * @param resource $resource
     *
     * @return resource|false
     */
    public function rewind($resource)
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
    public function fwrite_fflush($resource, string $data, ?int $length = null)
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
    public function fputs_fflush($resource, string $data, ?int $length = null)
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
    public function fpassthru_fflush($resource)
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
    public function file_exists($file)
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
    public function is_exists($file)
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
    public function is_file($file)
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
    public function is_realfile($file)
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
    public function is_dir($directory)
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
    public function is_realdir($directory)
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
    public function is_link($file)
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
    public function is_link_target($file)
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
    public function is_link_file($file)
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
    public function is_link_dir($directory)
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
    public function is_readable($file)
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
    public function is_writable($file)
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
    public function is_writeable($file)
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
    public function is_executable($file)
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
     * @return bool
     */
    public function clearstatcache($clear_realpath_cache = null, $file = null)
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
    public function realpath($file, $returnTargetPath = null)
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
    public function realpath_target($file)
    {
        return realpath($file);
    }

    /**
     * @param string $file
     *
     * @return string|false
     */
    public function realpath_link($file)
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
    public function chmod($file, $permissions)
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
    public function chown($file, $user)
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
    public function lchown($file, $user)
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
    public function chgrp($file, $group)
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
    public function lchgrp($file, $group)
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
    public function touch($file, $mtime = null, $atime = null)
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
    public function copy($from, $to, $context = null)
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
    public function rename($from, $to, $context = null)
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
    public function unlink($file, $context = null)
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
     * > если файла нет, то не выдаст ошибку
     *
     * @param string $file
     *
     * @return string|false
     */
    public function unlinkf($file, $context = null)
    {
        if (! is_file($file)) {
            return $file;
        }

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
    public function rm($file, $context = null)
    {
        $status = is_file($file) && ! is_link($file);

        if (false === $status) {
            return false;
        }

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
     * > удаляет в том числе символическую ссылку на файл
     * > если файла нет, то не выдаст ошибку
     *
     * @param string $file
     *
     * @return string|false
     */
    public function rmf($file, $context = null)
    {
        if (! is_file($file)) {
            return $file;
        }

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
     * @param string    $directory
     * @param int|null  $permissions
     * @param bool|null $recursive
     *
     * @return string|false
     */
    public function mkdir($directory, $permissions = null, $recursive = null, $context = null)
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
     * > если папка есть, то не выдаст ошибку
     *
     * @param string    $directory
     * @param int|null  $permissions
     * @param bool|null $recursive
     *
     * @return string|false
     */
    public function mkdirp($directory, $permissions = null, $recursive = null, $context = null)
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
    public function rmdir($directory, $context = null)
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
     * > если папки нет, то не выдаст ошибку
     *
     * @param string $directory
     *
     * @return string|false
     */
    public function rmdirf($directory, $context = null)
    {
        if (! is_dir($directory)) {
            return $directory;
        }

        if (is_link($directory)) {
            $status = (null !== $context)
                ? unlink($directory, $context)
                : unlink($directory);

        } else {
            $status = (null !== $context)
                ? rmdir($directory, $context)
                : rmdir($directory);
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
    public function link($target, $link)
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
    public function symlink($target, $link)
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
    public function file_read(
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
    public function file_read_flock(
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
    public function file_read_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = [],
        array $streamGetContentsArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        $fnTick = function (&$refResult) use (
            $file, $modeOpen, $modeLock,
            $fopenArgs,
            $flockArgs,
            $streamGetContentsArgs
        ) {
            $content = $this->file_read_flock(
                $file, $modeOpen, $modeLock,
                $fopenArgs,
                $flockArgs,
                $streamGetContentsArgs,
            );

            if (false === $content) {
                return;
            }

            $refResult = [ $content ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    /**
     * @param string                   $file
     * @param string                   $modeOpen
     * @param string|string[]|resource $data
     *
     * @return int|false
     */
    public function file_write(
        $file, $modeOpen,
        $data,
        array $fopenArgs = []
    )
    {
        array_unshift($fopenArgs, $file, $modeOpen);

        $resource = call_user_func_array([ $this, 'fopen' ], $fopenArgs);

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
    public function file_write_flock(
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
    public function file_write_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeOpen, $modeLock,
        $data,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        $fnTick = function (&$refResult) use (
            $file, $modeOpen, $modeLock,
            $data,
            $fopenArgs,
            $flockArgs
        ) {
            $len = $this->file_write_flock(
                $file, $modeOpen, $modeLock,
                $data,
                $fopenArgs,
                $flockArgs
            );

            if (false === $len) {
                return;
            }

            $refResult = [ $len ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    // > функция переименована в file_lines()
    // abstract public function file(string $file, int|null $flags = null, $context = null) : array;

    /**
     * @param string   $file
     * @param int|null $flags
     *
     * @return string[]|false
     */
    public function file_lines(
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
    public function file_lines_flock(
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
    public function file_lines_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeLock, $flags = null,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        $fnTick = function (&$refResult) use (
            $file, $modeLock, $flags,
            $fopenArgs,
            $flockArgs
        ) {
            $content = $this->file_lines_flock(
                $file, $modeLock, $flags,
                $fopenArgs,
                $flockArgs,
            );

            if (false === $content) {
                return;
            }

            $refResult = [ $content ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    // > функция переименована в file_echo()
    // abstract public function readfile(string $file, $context = null);

    /**
     * @param string $file
     *
     * @return int|false
     */
    public function file_echo(
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
    public function file_echo_flock(
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
    public function file_echo_flock_pooling(
        $tickUsleep, $timeoutMs,
        $file, $modeLock,
        array $fopenArgs = [],
        array $flockArgs = []
    )
    {
        $modeLock |= LOCK_NB;

        $fnTick = function (&$refResult) use (
            $file, $modeLock,
            $fopenArgs,
            $flockArgs
        ) {
            $size = $this->file_echo_flock(
                $file, $modeLock,
                $fopenArgs,
                $flockArgs,
            );

            if (false === $size) {
                return;
            }

            $refResult = [ $size ];
        };

        $result = Lib::php()->poolingSync($tickUsleep, $timeoutMs, $fnTick);

        return $result;
    }


    /**
     * > в случае отсутствия файла вернет NULL, а не FALSE
     *
     * @param string   $file
     * @param int|null $offset
     * @param int|null $length
     *
     * @return string|null|false
     */
    public function file_get_contents($file, $offset = null, $length = null, $context = null)
    {
        if (! file_exists($file)) {
            return null;

        } elseif (! is_file($file)) {
            return false;
        }

        if (! filesize($file)) {
            return null;
        }

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
     * > добавлена возможность автоматически создавать директорию, менять права на созданный файл
     *
     * @param string                   $file
     * @param string|string[]|resource $data
     * @param int|null                 $flags
     *
     * @return string|false
     */
    public function file_put_contents(
        $file, $data, $flags = null,
        array $filePutContentsArgs = [],
        ?array $mkdirpArgs = [],
        ?array $chmodIfNewArgs = null
    )
    {
        $hasMkdirp = (null !== $mkdirpArgs);
        $hasChmodIfNew = (null !== $chmodIfNewArgs) && (! file_exists($file));

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

        if ($hasChmodIfNew) {
            array_unshift($chmodIfNewArgs, $realpath);

            $status = call_user_func_array('chmod', $chmodIfNewArgs);

            if (false === $status) {
                return false;
            }
        }

        clearstatcache(true, $realpath);

        return $realpath;
    }
}
