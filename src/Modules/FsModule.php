<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Fs\FileSafe;
use Gzhegow\Lib\Modules\Fs\StreamSafe;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Modules\Fs\FileSafeContext;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\FilesystemException;


class FsModule
{
    /**
     * @var bool
     */
    protected $realpathReturnTargetPath = true;
    /**
     * @var int
     */
    protected $dirChmod = 0775;
    /**
     * @var int
     */
    protected $fileChmod = 0664;


    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new RuntimeException(
                'Missing PHP extension: fileinfo'
            );
        }
    }


    public function fileSafe(&$f = null) : FileSafe
    {
        return $f = new FileSafe();
    }

    public function streamSafe(&$s = null) : StreamSafe
    {
        return $s = new StreamSafe();
    }


    public function static_realpath_return_target_path(?bool $realpath_return_target_path = null) : bool
    {
        if (null !== $realpath_return_target_path) {
            $last = $this->realpathReturnTargetPath;

            $this->realpathReturnTargetPath = $realpath_return_target_path;

            $result = $last;
        }

        $result = $result ?? $this->realpathReturnTargetPath ?? true;

        return $result;
    }


    /**
     * @param int|string|null $dir_chmod
     */
    public function static_dir_chmod($dir_chmod = null) : int
    {
        if (null !== $dir_chmod) {
            if (! $this->type_chmod($dirChmodString, $dir_chmod)) {
                throw new LogicException(
                    [ 'The `dir_chmod` should be valid chmod', $dir_chmod ]
                );
            }

            $last = $this->dirChmod;

            $this->dirChmod = $dir_chmod;

            $result = $last;
        }

        $result = $result ?? $this->dirChmod ?? 0775;

        return $result;
    }

    /**
     * @param int|string|null $file_chmod
     */
    public function static_file_chmod($file_chmod = null) : int
    {
        if (null !== $file_chmod) {
            if (! $this->type_chmod($fileChmodString, $file_chmod)) {
                throw new LogicException(
                    [ 'The `file_chmod` should be valid chmod', $file_chmod ]
                );
            }

            $last = $this->fileChmod;

            $this->fileChmod = $file_chmod;

            $result = $last;
        }

        $result = $result ?? $this->fileChmod ?? 0664;

        return $result;
    }


    /**
     * @param int|null $result
     * @param string   $value
     *
     * @return bool
     */
    public function type_chmod(&$result, $value) : bool
    {
        $result = null;

        if (is_int($value)) {
            $int = $value;

        } elseif (is_string($value)) {
            if ('0' === $value) {
                $result = 0;

                return true;
            }

            $valueString = ltrim($value, '0');
            if ('' === $valueString) {
                return false;
            }

            if (! preg_match('/^[0124]?[0-7]{3}$/', $valueString)) {
                return false;
            }

            $int = octdec($valueString);
            if (0 === $int) {
                return false;
            }

        } else {
            return false;
        }

        if (($int < 0) || ($int > 04777)) {
            return false;
        }

        $octString = decoct($int);

        if (! in_array($octString[ 0 ], [ '0', '1', '2', '4' ], true)) {
            return false;
        }

        $result = $int;

        return true;
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_path(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withPathInfo = array_key_exists(0, $refs);
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
        }
        $refPathInfo = null;

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = Lib::php()->pathinfo($valueString);
            }
            catch ( \Throwable $e ) {
                unset($refPathInfo);

                return false;
            }
        }

        $result = $valueString;

        unset($refPathInfo);

        return true;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_realpath(
        &$result, $value,
        ?bool $isAllowSymlink = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $isAllowSymlink = $isAllowSymlink ?? true;

        $withPathInfo = array_key_exists(0, $refs);
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
        }
        $refPathInfo = null;

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        if (! $isAllowSymlink) {
            if (is_link($valueString)) {
                return false;
            }
        }

        $realpath = realpath($valueString);

        if (false === $realpath) {
            return false;
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = Lib::php()->pathinfo($realpath);
            }
            catch ( \Throwable $e ) {
                unset($refPathInfo);

                return false;
            }
        }

        $result = $realpath;

        unset($refPathInfo);

        return true;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_freepath(
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        if (! $this->type_path($_value, $value, $refs)) {
            return false;
        }

        if (file_exists($_value)) {
            return false;
        }

        $result = $_value;

        return true;
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_dirpath(
        &$result, $value,
        ?bool $isAllowExists = null, ?bool $isAllowSymlink = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $isAllowExists = $isAllowExists ?? true;
        $isAllowSymlink = $isAllowSymlink ?? true;

        if (! $this->type_path($_value, $value, $refs)) {
            return false;
        }

        $exists = file_exists($_value);

        if (! $isAllowExists) {
            if ($exists) {
                return false;
            }

            $result = $_value;

            return true;
        }

        if ($exists) {
            if (! is_dir($_value)) {
                return false;
            }

            if (! $isAllowSymlink) {
                if (is_link($_value)) {
                    return false;
                }
            }

            $realpath = realpath($_value);

            if (false === $realpath) {
                return false;
            }

            $_value = $realpath;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_dirpath_realpath(
        &$result, $value,
        ?bool $isAllowSymlink = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $isAllowSymlink = $isAllowSymlink ?? true;

        $status = $this->type_realpath(
            $_value, $value,
            $isAllowSymlink,
            $refs
        );

        if (! $status) {
            return false;
        }

        if (! $isAllowSymlink) {
            if (is_link($value)) {
                return false;
            }
        }

        if (is_dir($_value)) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_filepath(
        &$result, $value, ?bool $isAllowExists,
        ?bool $isAllowSymlink = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $isAllowExists = $isAllowExists ?? true;
        $isAllowSymlink = $isAllowSymlink ?? true;

        if (! $this->type_path($_value, $value, $refs)) {
            return false;
        }

        $exists = file_exists($_value);

        if (! $isAllowExists) {
            if ($exists) {
                return false;
            }

            $result = $_value;

            return true;
        }

        if ($exists) {
            if (! $isAllowSymlink) {
                if (is_link($_value)) {
                    return false;
                }
            }

            if (! is_file($_value)) {
                return false;
            }

            $realpath = realpath($_value);

            if (false === $realpath) {
                return false;
            }

            $_value = $realpath;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_filepath_realpath(
        &$result, $value,
        ?bool $isAllowSymlink = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $status = $this->type_realpath(
            $_value, $value,
            $isAllowSymlink,
            $refs
        );

        if (! $status) {
            return false;
        }

        if (! $isAllowSymlink) {
            if (is_link($value)) {
                return false;
            }
        }

        if (is_file($_value)) {
            $result = $_value;

            return true;
        }

        return false;
    }


    /**
     * @param string|null $result
     */
    public function type_filename(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        $forbidden = [ "/", "\\", DIRECTORY_SEPARATOR ];

        $isForbidden = false;
        foreach ( $forbidden as $f ) {
            if (false !== strpos($valueString, $f)) {
                $isForbidden = true;

                break;
            }
        }

        if (! $isForbidden) {
            $result = $valueString;

            return true;
        }

        return false;
    }


    /**
     * @param \SplFileInfo|null $result
     */
    public function type_file(
        &$result, $value,
        ?array $extensions = null, ?array $mimeTypes = null,
        ?array $filters = null
    ) : bool
    {
        $result = null;

        $splFileInfo = null;
        if ($value instanceof \SplFileInfo) {
            $splFileInfo = $value;

        } else {
            if (! $this->type_filepath_realpath($realpath, $value)) {
                return false;
            }

            try {
                $splFileInfo = new \SplFileInfo($realpath);
            }
            catch ( \Throwable $e ) {
            }
        }

        if (null !== $extensions) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->type_file_extensions($splFileInfo, $extensions);
            }
        }

        if (null !== $mimeTypes) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->type_file_mime_types($splFileInfo, $mimeTypes);
            }
        }

        if (null !== $filters) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->type_file_filters($splFileInfo, $filters);
            }
        }

        if (null !== $splFileInfo) {
            $result = $splFileInfo;

            return true;
        }

        return false;
    }

    protected function type_file_extensions(\SplFileInfo $splFileInfo, array $extensions) : ?\SplFileInfo
    {
        if ([] === $extensions) {
            return null;
        }

        $fileRealpath = $splFileInfo->getRealPath();

        $fileExtensions = $this->extensions($fileRealpath);

        if (null === $fileExtensions) {
            return null;
        }

        if (in_array($fileExtensions, $extensions, true)) {
            return $splFileInfo;
        }

        $fileExtensionLast = explode('.', $fileExtensions);
        $fileExtensionLast = end($fileExtensionLast);

        if (in_array($fileExtensionLast, $extensions, true)) {
            return $splFileInfo;
        }

        return null;
    }

    protected function type_file_mime_types(\SplFileInfo $splFileInfo, array $mimeTypes) : ?\SplFileInfo
    {
        if ([] === $mimeTypes) {
            return null;
        }

        $fileRealpath = $splFileInfo->getRealPath();

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        $mimeType = finfo_file($finfo, $fileRealpath);

        finfo_close($finfo);

        if (false === $mimeType) {
            return null;
        }

        if (in_array($mimeType, $mimeTypes, true)) {
            return $splFileInfo;
        }

        foreach ( $mimeTypes as $strStarts ) {
            if (0 === strpos($mimeType, $strStarts)) {
                return $splFileInfo;
            }
        }

        return null;
    }

    protected function type_file_filters(\SplFileInfo $splFileInfo, array $filters) : ?\SplFileInfo
    {
        if ([] === $filters) {
            return $splFileInfo;
        }

        $filtersList = [
            'max_size' => true,
            'min_size' => true,
        ];

        $filtersIntersect = array_intersect_key($filters, $filtersList);

        if ([] !== $filtersIntersect) {
            $hasMaxSize = array_key_exists('max_size', $filters);
            $hasMinSize = array_key_exists('min_size', $filters);

            $theFormat = null;
            $fileSize = null;
            if ($hasMaxSize || $hasMinSize) {
                $theFormat = Lib::format();

                $fileSize = $splFileInfo->getSize();
            }

            foreach ( $filters as $filter => $value ) {
                if ('max_size' === $filter) {
                    $maxSize = $theFormat->bytes_decode($value, [ NAN ]);

                    if (! ($fileSize <= $maxSize)) {
                        return null;
                    }

                } elseif ('min_size' === $filter) {
                    $minSize = $theFormat->bytes_decode($value, [ NAN ]);

                    if (! ($fileSize >= $minSize)) {
                        return null;
                    }
                }
            }
        }

        return $splFileInfo;
    }


    /**
     * @param \SplFileInfo|null $result
     */
    public function type_image(
        &$result, $value,
        ?array $extensions = null, ?array $mimeTypes = null,
        ?array $filters = null
    ) : bool
    {
        $result = null;

        $status = $this->type_file(
            $splFileInfo, $value,
            $extensions, $mimeTypes,
            $filters
        );

        if (! $status) {
            return false;
        }

        if (null !== $filters) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->type_image_filters($splFileInfo, $filters);
            }
        }

        if (null !== $splFileInfo) {
            $result = $splFileInfo;

            return true;
        }

        return false;
    }

    protected function type_image_filters(\SplFileInfo $splFileInfo, array $filters) : ?\SplFileInfo
    {
        if ([] === $filters) {
            return $splFileInfo;
        }

        $filtersList = [
            'max_width'  => true,
            'max_height' => true,
            'min_width'  => true,
            'min_height' => true,
            'width'      => true,
            'height'     => true,
            'ratio'      => true,
        ];

        $filtersIntersect = array_intersect_key($filters, $filtersList);

        if ([] !== $filtersIntersect) {
            $fileRealpath = $splFileInfo->getRealPath();

            try {
                $array = getimagesize($fileRealpath);
            }
            catch ( \Throwable $e ) {
                $array = false;
            }

            if (false === $array) {
                return null;
            }

            [ $imageWidth, $imageHeight ] = $array;

            $theType = Lib::type();

            foreach ( $filters as $filter => $value ) {
                if ('max_width' === $filter) {
                    if (! $theType->numeric_int_positive($maxWidth, $value)) {
                        return null;
                    }

                    if (! ($imageWidth <= $maxWidth)) {
                        return null;
                    }

                } elseif ('max_height' === $filter) {
                    if (! $theType->numeric_int_positive($maxHeight, $value)) {
                        return null;
                    }

                    if (! ($imageHeight <= $maxHeight)) {
                        return null;
                    }

                } elseif ('min_width' === $filter) {
                    if (! $theType->numeric_int_positive($minWidth, $value)) {
                        return null;
                    }

                    if (! ($imageWidth >= $minWidth)) {
                        return null;
                    }

                } elseif ('min_height' === $filter) {
                    if (! $theType->numeric_int_positive($minHeight, $value)) {
                        return null;
                    }

                    if (! ($imageHeight >= $minHeight)) {
                        return null;
                    }

                } elseif ('width' === $filter) {
                    if (! $theType->numeric_int_positive($exactWidth, $value)) {
                        return null;
                    }

                    if (! ($imageWidth == $exactWidth)) {
                        return null;
                    }

                } elseif ('height' === $filter) {
                    if (! $theType->numeric_int_positive($exactHeight, $value)) {
                        return null;
                    }

                    if (! ($imageHeight == $exactHeight)) {
                        return null;
                    }

                } elseif ('ratio' === $filter) {
                    if ($theType->numeric($number, $value)) {
                        $ratio = $number;
                        $ratio = round($ratio, 3);

                    } elseif ($theType->string_not_empty($string, $value)) {
                        [ $ratioW, $ratioH ] = explode('/', $string) + [ 0, 0 ];

                        if (! $theType->numeric_int_positive($ratioWInt, $ratioW)) {
                            return null;
                        }
                        if (! $theType->numeric_int_positive($ratioHInt, $ratioH)) {
                            return null;
                        }

                        $ratio = $ratioW / $ratioH;
                        $ratio = round($ratio, 3);

                    } else {
                        return null;
                    }

                    $imageRatio = $imageWidth / $imageHeight;
                    $imageRatio = round($imageRatio, 3);

                    if ($ratio !== $imageRatio) {
                        return null;
                    }
                }
            }
        }

        return $splFileInfo;
    }


    public function get_umask_chmod(int $fileMode, int $dirMode) : int
    {
        $fileMode &= 0777;
        $dirMode &= 0777;

        $defaultFileMask = 0664;
        $defaultDirMask = 0775;

        $fileUmask = $defaultFileMask & (~$fileMode);
        $dirUmask = $defaultDirMask & (~$dirMode);

        if ($fileUmask !== $dirUmask) {
            throw new RuntimeException(
                [
                    'Unable to create one umask for both given `fileMode` and `dirMode`',
                    //
                    $fileMode,
                    $dirMode,
                    $fileUmask,
                    $dirUmask,
                ]
            );
        }

        return $fileUmask;
    }


    public function pathinfo(string $path, ?int $flags = null, ?string $separator = null) : array
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        return Lib::php()->pathinfo($path, $flags, $separatorString, '.');
    }

    public function dirname(string $path, ?int $levels = null, ?string $separator = null) : ?string
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        return Lib::php()->dirname($path, $separatorString, $levels);
    }

    public function basename(string $path, ?string $extension = null) : ?string
    {
        return Lib::php()->basename($path, $extension);
    }

    public function filename(string $path) : ?string
    {
        return Lib::php()->filename($path, '.');
    }

    public function fname(string $path) : ?string
    {
        return Lib::php()->fname($path, '.');
    }

    public function extension(string $path) : ?string
    {
        return Lib::php()->extension($path, '.');
    }

    public function extensions(string $path) : ?string
    {
        return Lib::php()->extensions($path, '.');
    }


    public function path_normalize(string $path, ?string $separator = null) : string
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if ($this->type_realpath($realpath, $path)) {
            $normalized = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);

        } else {
            $normalized = Lib::php()->path_normalize($path, $separator);
        }

        return $normalized;
    }

    public function path_resolve(string $path, ?string $separator = null) : string
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if ($this->type_realpath($realpath, $path)) {
            $resolved = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);

        } else {
            $resolved = Lib::php()->path_resolve($path, $separatorString, '.');
        }

        return $resolved;
    }


    public function path_relative(
        string $path, string $root,
        ?string $separator = null
    ) : string
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if ($this->type_realpath($realpath, $path)) {
            $path = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);
        }

        if ($this->type_realpath($realpath, $root)) {
            $root = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);
        }

        return Lib::php()->path_relative($path, $root, $separatorString, '.');
    }

    public function path_absolute(
        string $path, string $current,
        ?string $separator = null
    ) : string
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if ($this->type_realpath($realpath, $path)) {
            $path = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);
        }

        if ($this->type_realpath($realpath, $current)) {
            $current = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);
        }

        return Lib::php()->path_absolute($path, $current, $separatorString, '.');
    }

    public function path_or_absolute(
        string $path, string $current,
        ?string $separator = null
    ) : string
    {
        if (! Lib::type()->char($separatorString, $separator ?? DIRECTORY_SEPARATOR)) {
            throw new LogicException(
                [ 'The `separator` should be char', $separator ]
            );
        }

        if ($this->type_realpath($realpath, $path)) {
            $path = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);
        }

        if ($this->type_realpath($realpath, $current)) {
            $current = str_replace(DIRECTORY_SEPARATOR, $separatorString, $realpath);
        }

        return Lib::php()->path_or_absolute($path, $current, $separatorString, '.');
    }


    public function lpush(
        string $file, string $data
    ) : bool
    {
        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();
        $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $file, $data
            ) {
                $fileIn = "{$file}.in";
                $fileInLock = "{$file}.in.lock";

                if ($fhInLock = $f->fopen_flock(
                    $fileInLock, 'w', LOCK_EX | LOCK_NB,
                )) {
                    $ctx->onFinallyFrelease($fhInLock);
                    $ctx->onFinallyFclose($fhInLock);
                    $ctx->onFinallyUnlink($fileInLock);

                    fwrite($fhInLock, getmypid());

                    $line = base64_encode($data);

                    file_put_contents($fileIn, $line . "\n" . file_get_contents($fileIn));
                }
            }
        );

        return true;
    }

    public function blpush(
        $flockWaitTickUsleep, $flockWaitTimeoutMs,
        string $file, string $data
    ) : bool
    {
        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();
        $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $flockWaitTickUsleep, $flockWaitTimeoutMs,
                $file, $data
            ) {
                $fileIn = "{$file}.in";
                $fileInLock = "{$file}.in.lock";

                if ($fhInLock = $f->fopen_flock_pooling(
                    $flockWaitTickUsleep, $flockWaitTimeoutMs,
                    $fileInLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhInLock);
                    $ctx->onFinallyFclose($fhInLock);
                    $ctx->onFinallyUnlink($fileInLock);

                    fwrite($fhInLock, getmypid());

                    $line = base64_encode($data);

                    file_put_contents($fileIn, $line . "\n" . file_get_contents($fileIn));
                }
            }
        );

        return true;
    }


    public function rpush(string $file, string $data) : bool
    {
        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();
        $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $file, $data
            ) {
                $fileIn = "{$file}.in";
                $fileInLock = "{$file}.in.lock";

                if ($fhInLock = $f->fopen_flock(
                    $fileInLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhInLock);
                    $ctx->onFinallyFclose($fhInLock);
                    $ctx->onFinallyUnlink($fileInLock);

                    fwrite($fhInLock, getmypid());

                    $line = base64_encode($data);

                    file_put_contents($fileIn, $line . "\n", FILE_APPEND);
                }
            }
        );

        return true;
    }

    public function brpush(
        $flockWaitTickUsleep, $flockWaitTimeoutMs,
        string $file, string $data
    ) : bool
    {
        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();
        $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $flockWaitTickUsleep, $flockWaitTimeoutMs,
                $file, $data
            ) {
                $fileIn = "{$file}.in";
                $fileInLock = "{$file}.in.lock";

                if ($fhInLock = $f->fopen_flock_pooling(
                    $flockWaitTickUsleep, $flockWaitTimeoutMs,
                    $fileInLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhInLock);
                    $ctx->onFinallyFclose($fhInLock);
                    $ctx->onFinallyUnlink($fileInLock);

                    fwrite($fhInLock, getmypid());

                    $line = base64_encode($data);

                    file_put_contents($fileIn, $line . "\n", FILE_APPEND);
                }
            }
        );

        return true;
    }


    public function lpop(string $file, ?bool $deleteIfEmpty = null) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();

        $data = $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $file, $deleteIfEmpty
            ) {
                $fileIn = "{$file}.in";
                $fileOut = "{$file}.lpop";

                if ($deleteIfEmpty) {
                    $ctx->onFinallyUnlinkIfEmpty($fileIn);
                    $ctx->onFinallyUnlinkIfEmpty($fileOut);
                }

                $fileOutLock = "{$file}.lpop.lock";

                $data = null;

                if ($fhOutLock = $f->fopen_flock(
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhOutLock);
                    $ctx->onFinallyFclose($fhOutLock);
                    $ctx->onFinallyUnlink($fileOutLock);

                    fwrite($fhOutLock, getmypid());

                    $isFileOut = is_file($fileOut) && filesize($fileOut);

                    if (! $isFileOut) {
                        $isFileIn = is_file($fileIn) && filesize($fileIn);

                        if ($isFileIn) {
                            $content = file_get_contents($fileIn);

                            if (! ((false === $content) || ('' === $content))) {
                                file_put_contents($fileOut, $content, FILE_APPEND);
                                file_put_contents($fileIn, '');

                                $isFileOut = true;
                            }
                        }
                    }

                    if ($isFileOut) {
                        if ($fhOut = fopen($fileOut, 'r+')) {
                            $ctx->onFinallyFclose($fhOut);

                            $line = fgets($fhOut);
                            $contentOut = stream_get_contents($fhOut);

                            rewind($fhOut);
                            ftruncate($fhOut, 0);

                            if ('' !== $contentOut) {
                                fwrite($fhOut, $contentOut);
                            }

                            $line = rtrim($line);
                            if ('' !== $line) {
                                $data = base64_decode($line);
                            }
                        }
                    }
                }

                return $data;
            }
        );

        return $data;
    }

    public function blpop(
        $blockTickUsleep, $blockTimeoutMs,
        string $file, ?bool $deleteIfEmpty = null
    ) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();

        $data = $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $blockTickUsleep, $blockTimeoutMs,
                $file, $deleteIfEmpty
            ) {
                $fileIn = "{$file}.in";
                $fileOut = "{$file}.lpop";

                if ($deleteIfEmpty) {
                    $ctx->onFinallyUnlinkIfEmpty($fileIn);
                    $ctx->onFinallyUnlinkIfEmpty($fileOut);
                }

                $data = null;

                $fileOutLock = "{$file}.lpop.lock";

                if ($fhOutLock = $f->fopen_flock_pooling(
                    $blockTickUsleep, $blockTimeoutMs,
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhOutLock);
                    $ctx->onFinallyFclose($fhOutLock);
                    $ctx->onFinallyUnlink($fileOutLock);

                    fwrite($fhOutLock, getmypid());

                    $fnTick = static function (&$result) use (
                        $ctx,
                        $fileIn, $fileOut,
                        &$fhOut
                    ) {
                        $isFileOut = is_file($fileOut) && filesize($fileOut);

                        if (! $isFileOut) {
                            $isFileIn = is_file($fileIn) && filesize($fileIn);

                            if ($isFileIn) {
                                $content = file_get_contents($fileIn);

                                if (! ((false === $content) || ('' === $content))) {
                                    file_put_contents($fileOut, $content, FILE_APPEND);
                                    file_put_contents($fileIn, '');

                                    $isFileOut = true;
                                }
                            }
                        }

                        if ($isFileOut) {
                            if (! $fhOut) {
                                if ($fhOut = fopen($fileOut, 'r+')) {
                                    $ctx->onFinallyFclose($fhOut);
                                }
                            }

                            if ($fhOut) {
                                $line = fgets($fhOut);
                                $contentOut = stream_get_contents($fhOut);

                                rewind($fhOut);
                                ftruncate($fhOut, 0);

                                if ('' !== $contentOut) {
                                    fwrite($fhOut, $contentOut);
                                }

                                $line = rtrim($line);
                                if ('' !== $line) {
                                    $data = base64_decode($line);

                                    $result = [ $data ];
                                }
                            }
                        }
                    };

                    $data = Lib::php()->poolingSync($blockTickUsleep, $blockTimeoutMs, $fnTick);

                    if (false === $data) {
                        $data = null;
                    }
                }

                return $data;
            }
        );

        return $data;
    }


    public function rpop(string $file, ?bool $deleteIfEmpty = null) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();

        $data = $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $file, $deleteIfEmpty
            ) {
                $fileIn = "{$file}.in";
                $fileOut = "{$file}.rpop";

                if ($deleteIfEmpty) {
                    $ctx->onFinallyUnlinkIfEmpty($fileIn);
                    $ctx->onFinallyUnlinkIfEmpty($fileOut);
                }

                $fileOutLock = "{$file}.rpop.lock";

                $data = null;

                if ($fhOutLock = $f->fopen_flock(
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhOutLock);
                    $ctx->onFinallyFclose($fhOutLock);
                    $ctx->onFinallyUnlink($fileOutLock);

                    $isFileOut = is_file($fileOut) && filesize($fileOut);

                    if (! $isFileOut) {
                        $isFileIn = is_file($fileIn) && filesize($fileIn);

                        if ($isFileIn) {
                            $lines = file($fileIn);

                            if (! ((false === $lines) || ([] === $lines))) {
                                $lines = array_map('trim', $lines);
                                $lines = array_reverse($lines);

                                $content = implode("\n", $lines);

                                file_put_contents($fileOut, $content, FILE_APPEND);
                                file_put_contents($fileIn, '');

                                $isFileOut = true;
                            }
                        }
                    }

                    if ($isFileOut) {
                        if ($fhOut = fopen($fileOut, 'r+')) {
                            $ctx->onFinallyFclose($fhOut);

                            $line = fgets($fhOut);
                            $contentOut = stream_get_contents($fhOut);

                            rewind($fhOut);
                            ftruncate($fhOut, 0);

                            if ('' !== $contentOut) {
                                fwrite($fhOut, $contentOut);
                            }

                            $line = rtrim($line);
                            if ('' !== $line) {
                                $data = base64_decode($line);
                            }
                        }
                    }
                }

                return $data;
            }
        );

        return $data;
    }

    public function brpop(
        $blockTickUsleep, $blockTimeoutMs,
        string $file, ?bool $deleteIfEmpty = null
    ) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $theType = Lib::type();

        if (! $theType->freepath($fileString, $file)) {
            throw new LogicException(
                [ 'The `file` should be valid freepath (file should be not exists)', $file ]
            );
        }

        if (! $theType->dirpath_realpath($var, $fileDir = dirname($fileString))) {
            throw new LogicException(
                [ 'The `fileDir` should be existing directory', $fileDir ]
            );
        }

        $f = $this->fileSafe();

        $data = $f->call(
            static function (FileSafeContext $ctx) use (
                $f,
                $blockTickUsleep, $blockTimeoutMs,
                $file, $deleteIfEmpty
            ) {
                $fileIn = "{$file}.in";
                $fileOut = "{$file}.rpop";

                if ($deleteIfEmpty) {
                    $ctx->onFinallyUnlinkIfEmpty($fileIn);
                    $ctx->onFinallyUnlinkIfEmpty($fileOut);
                }

                $fileOutLock = "{$file}.rpop.lock";

                $data = null;

                if ($fhOutLock = $f->fopen_flock_pooling(
                    $blockTickUsleep, $blockTimeoutMs,
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    $ctx->onFinallyFrelease($fhOutLock);
                    $ctx->onFinallyFclose($fhOutLock);
                    $ctx->onFinallyUnlink($fileOutLock);

                    fwrite($fhOutLock, getmypid());

                    $fnTick = static function (&$result) use (
                        &$fhOut, $fileIn, $fileOut,
                        //
                        $ctx
                    ) {
                        $isFileOut = is_file($fileOut) && filesize($fileOut);

                        if (! $isFileOut) {
                            $isFileIn = is_file($fileIn) && filesize($fileIn);

                            if ($isFileIn) {
                                $lines = file($fileIn);

                                if (! ((false === $lines) || ([] === $lines))) {
                                    $lines = array_map('trim', $lines);
                                    $lines = array_reverse($lines);

                                    $content = implode("\n", $lines);

                                    file_put_contents($fileOut, $content, FILE_APPEND);
                                    file_put_contents($fileIn, '');

                                    $isFileOut = true;
                                }
                            }
                        }

                        if ($isFileOut) {
                            if (! $fhOut) {
                                if ($fhOut = fopen($fileOut, 'r+')) {
                                    $ctx->onFinallyFclose($fhOut);
                                }
                            }

                            if ($fhOut) {
                                $line = fgets($fhOut);
                                $contentOut = stream_get_contents($fhOut);

                                rewind($fhOut);
                                ftruncate($fhOut, 0);

                                if ('' === $contentOut) {
                                    fwrite($fhOut, $contentOut);
                                }

                                $line = rtrim($line);
                                if ('' !== $line) {
                                    $data = base64_decode($line);

                                    $result = [ $data ];
                                }
                            }
                        }
                    };

                    $data = Lib::php()->poolingSync($blockTickUsleep, $blockTimeoutMs, $fnTick);

                    if (false === $data) {
                        $data = null;
                    }
                }

                return $data;
            }
        );

        return $data;
    }


    /**
     * @return \Generator<\SplFileInfo>
     */
    public function dir_walk_it(
        string $dirpath,
        ?array $recursiveDirectoryIteratorArgs = null,
        ?array $recursiveIteratorIteratorArgs = null
    ) : \Generator
    {
        $recursiveDirectoryIteratorArgs = $recursiveDirectoryIteratorArgs ?: [ \FilesystemIterator::SKIP_DOTS ];
        $recursiveIteratorIteratorArgs = $recursiveIteratorIteratorArgs ?: [ \RecursiveIteratorIterator::CHILD_FIRST ];

        if (! $this->type_dirpath_realpath($_dirpath, $dirpath)) {
            throw new FilesystemException(
                [ 'Directory not exists', $dirpath ]
            );
        }

        $it = new \RecursiveDirectoryIterator(
            $_dirpath,
            ...$recursiveDirectoryIteratorArgs
        );

        $iit = new \RecursiveIteratorIterator(
            $it,
            ...$recursiveIteratorIteratorArgs
        );

        foreach ( $iit as $i => $spl ) {
            /** @var \SplFileInfo $fileInfo */

            yield $i => $spl;
        }
    }


    /**
     * @return array<int, array{
     *     0: int,
     *     1: array{ 0: int, 1: string },
     *     2: array{ 0: int, 1: string }
     * }>
     */
    public function file_diff($file1, $file2, ?int $length = null, ?int $lengthBuffer = null) : array
    {
        $length = $length ?? 8192;                      // > 8kb
        $lengthBuffer = $lengthBuffer ?? (1024 * 1024); // > 1mb

        if ($length < 1) {
            throw new LogicException(
                [ 'The `length` should be greater than zero', $length ]
            );
        }

        $fh1 = fopen($file1, 'rb');
        $fh2 = fopen($file2, 'rb');

        if (false === $fh1) {
            throw new FilesystemException(
                [ 'Unable to open file with flags: rb', $file1 ]
            );
        }
        if (false === $fh2) {
            throw new FilesystemException(
                [ 'Unable to open file with flags: rb', $file2 ]
            );
        }

        $lineNumber = 1;
        $buffer1 = '';
        $buffer2 = '';

        $diff = [];

        while ( true
            && ! feof($fh1)
            && ! feof($fh2)
        ) {
            $chunk1 = fread($fh1, $length);
            $chunk2 = fread($fh2, $length);

            $len1 = strlen($chunk1);
            $len2 = strlen($chunk2);

            $buffer1 .= $chunk1;
            $buffer2 .= $chunk2;

            if ($len1 !== $len2) {
                $maxlen = max(
                    strlen($buffer1),
                    strlen($buffer2)
                );

                $line1 = substr($buffer1, 0, $maxlen);
                $line2 = substr($buffer2, 0, $maxlen);

                if ($line1 !== $line2) {
                    $diff[] = [
                        $lineNumber,
                        [ strlen($line1), $line1 ],
                        [ strlen($line2), $line2 ],
                    ];
                }

                $buffer1 = '';
                $buffer2 = '';

            } else {
                if ($chunk1 !== $chunk2) {
                    $hasNPos1 = (false !== ($nPos1 = strpos($buffer1, "\n")));
                    $hasNPos2 = (false !== ($nPos2 = strpos($buffer2, "\n")));

                    if ($hasNPos1 && $hasNPos2) {
                        while ( true
                            && (false !== ($nPos1 = strpos($buffer1, "\n")))
                            && (false !== ($nPos2 = strpos($buffer2, "\n")))
                        ) {
                            $line1 = substr($buffer1, 0, $nPos1 + 1);
                            $line2 = substr($buffer2, 0, $nPos2 + 1);

                            $buffer1 = substr($buffer1, $nPos1 + 1);
                            $buffer2 = substr($buffer2, $nPos2 + 1);

                            if ($line1 !== $line2) {
                                $diff[] = [
                                    $lineNumber,
                                    [ strlen($line1), $line1 ],
                                    [ strlen($line2), $line2 ],
                                ];
                            }

                            $lineNumber++;
                        }

                    } else {
                        if ($hasNPos1) {
                            $line1 = substr($buffer1, 0, $nPos1 + 1);
                            $line2 = substr($buffer2, 0, $nPos1 + 1);

                            $buffer1 = substr($buffer1, $nPos1 + 1);

                            while ( ! feof($fh2) ) {
                                $buffer2 = fgets($fh2, $lengthBuffer);

                                if ($buffer2[ strlen($buffer2) - 1 ] === "\n") {
                                    break;
                                }
                            }

                            $buffer2 = '';

                        } elseif ($hasNPos2) {
                            $line1 = substr($buffer1, 0, $nPos2 + 1);
                            $line2 = substr($buffer2, 0, $nPos2 + 1);

                            $buffer2 = substr($buffer2, $nPos2 + 1);

                            while ( ! feof($fh1) ) {
                                $buffer1 = fgets($fh1, $lengthBuffer);

                                if ($buffer1[ strlen($buffer1) - 1 ] === "\n") {
                                    break;
                                }
                            }

                            $buffer1 = '';

                        } else {
                            $minlen = min(
                                strlen($buffer1),
                                strlen($buffer2)
                            );

                            $line1 = substr($buffer1, 0, $minlen);
                            $line2 = substr($buffer2, 0, $minlen);

                            $hasFh1 = true;
                            $hasFh2 = true;
                            while (
                                ($hasFh1 = $hasFh1 && (! feof($fh1)))
                                || ($hasFh2 = $hasFh2 && (! feof($fh2)))
                            ) {
                                $buffer1 = fgets($fh1, $lengthBuffer);
                                $buffer2 = fgets($fh2, $lengthBuffer);

                                if ($buffer1[ strlen($buffer1) - 1 ] === "\n") {
                                    $hasFh1 = false;
                                }
                                if ($buffer2[ strlen($buffer2) - 1 ] === "\n") {
                                    $hasFh2 = false;
                                }

                                if (! $hasFh1 && ! $hasFh2) {
                                    break;
                                }
                            }

                            $buffer1 = '';
                            $buffer2 = '';
                        }

                        if ($line1 !== $line2) {
                            $diff[] = [
                                $lineNumber,
                                [ strlen($line1), $line1 ],
                                [ strlen($line2), $line2 ],
                            ];
                        }

                        $lineNumber++;
                    }
                }
            }
        }

        fclose($fh1);
        fclose($fh2);

        return $diff;
    }


    public function file_replace_blocks(
        string $filepath,
        string $start, $lines, ?string $end = null
    ) : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        if (! file_exists($filepath)) {
            file_put_contents($filepath, '');

            $filepathRealpath = realpath($filepath);
        }

        if (! $theType->trim($startTrim, $start, true)) {
            throw new LogicException(
                [ 'The `start` should be non-empty trim', $start ]
            );
        }

        if (! is_null($endTrim = $end)) {
            if (! $theType->trim($endTrim, $end)) {
                throw new LogicException(
                    [ 'The `end` should be non-empty trim', $end ]
                );
            }
        }

        if ($startTrim === $endTrim) {
            throw new LogicException(
                [ 'The `start` should not be equal to `end`', $startTrim, $endTrim ]
            );
        }

        $input = fopen($filepathRealpath, 'r');
        if (false === $input) {
            throw new FilesystemException(
                [ 'Unable to perform fopen() on file', $filepathRealpath ]
            );
        }

        $filepathRealpathTmp = $filepathRealpath . '.tmp';

        $output = fopen($filepathRealpathTmp, 'w');
        if (false === $output) {
            throw new FilesystemException(
                [ 'Unable to perform fopen() on file', $filepathRealpathTmp ]
            );
        }

        $insideBlock = null;

        while ( ! feof($input) ) {
            $fgets = fgets($input);

            $fgets = rtrim($fgets);

            if ('' !== $startTrim) {
                if (ltrim($fgets) === $startTrim) {
                    $insideBlock = 1;
                }
            }

            if ('' !== $endTrim) {
                if (ltrim($fgets) === $endTrim) {
                    $insideBlock = 0;
                }
            }

            if (! $insideBlock) {
                fwrite($output, $fgets . "\n");

            } elseif (1 === $insideBlock) {
                fwrite($output, $startTrim . "\n");

                foreach ( $thePhp->to_list_it($lines) as $line ) {
                    if (is_array($line)) {
                        continue;
                    }

                    $line = rtrim($line);

                    fwrite($output, $line . "\n");
                }

                fwrite($output, $endTrim . "\n");

                $insideBlock = 2;
            }
        }

        if (null === $insideBlock) {
            fwrite($output, "\n");

            fwrite($output, $startTrim . "\n");

            foreach ( $thePhp->to_list_it($lines) as $line ) {
                if (is_array($line)) {
                    continue;
                }

                $line = trim($line);

                fwrite($output, $line . "\n");
            }

            fwrite($output, $endTrim . "\n");
        }

        fclose($input);
        fclose($output);

        $status = rename($filepathRealpathTmp, $filepathRealpath);

        if (false === $status) {
            throw new FilesystemException(
                [ 'Unable to perform rename() on file', $filepathRealpathTmp ]
            );
        }

        return $filepathRealpath;
    }
}
