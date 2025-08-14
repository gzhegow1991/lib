<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Modules\Fs\FileSafe\FileSafe;
use Gzhegow\Lib\Modules\Fs\StreamSafe\StreamSafe;
use Gzhegow\Lib\Modules\Fs\SocketSafe\SocketSafe;
use Gzhegow\Lib\Modules\Fs\FileSafe\FileSafeProxy;
use Gzhegow\Lib\Exception\Runtime\ExtensionException;
use Gzhegow\Lib\Modules\Fs\StreamSafe\StreamSafeProxy;
use Gzhegow\Lib\Exception\Runtime\FilesystemException;
use Gzhegow\Lib\Modules\Fs\SocketSafe\SocketSafeProxy;


class FsModule
{
    /**
     * @var bool
     */
    protected static $realpathReturnTargetPath = true;
    /**
     * @var int
     */
    protected static $dirChmod = 0775;
    /**
     * @var int
     */
    protected static $fileChmod = 0664;

    public static function staticRealpathReturnTargetPath(?bool $realpath_return_target_path = null) : bool
    {
        $last = static::$realpathReturnTargetPath;

        if (null !== $realpath_return_target_path) {
            static::$realpathReturnTargetPath = $realpath_return_target_path;
        }

        static::$realpathReturnTargetPath = static::$realpathReturnTargetPath ?? true;

        return $last;
    }

    /**
     * @param int|string|null $dirChmod
     */
    public static function staticDirChmod($dirChmod = null) : int
    {
        $last = static::$dirChmod;

        if (null !== $dirChmod) {
            $theType = Lib::type();

            $dirChmodValid = $theType->chmod($dirChmod)->orThrow();

            static::$dirChmod = $dirChmodValid;
        }

        static::$dirChmod = static::$dirChmod ?? 0775;

        return $last;
    }

    /**
     * @param int|string|null $fileChmod
     */
    public static function staticFileChmod($fileChmod = null) : int
    {
        $last = static::$fileChmod;

        if (null !== $fileChmod) {
            $theType = Lib::type();

            $fileChmodValid = $theType->chmod($fileChmod)->orThrow();

            static::$fileChmod = $fileChmodValid;
        }

        static::$fileChmod = static::$fileChmod ?? 0664;

        return $last;
    }


    /**
     * @var SocketSafe
     */
    protected $fileSafe;

    /**
     * @var SocketSafe
     */
    protected $socketSafe;

    /**
     * @var StreamSafe
     */
    protected $streamSafe;


    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new ExtensionException(
                'Missing PHP extension: fileinfo'
            );
        }
    }


    public function newFileSafe() : FileSafeProxy
    {
        $fileSafe = $this->createFileSafe();

        return new FileSafeProxy($fileSafe);
    }

    public function cloneFileSafe() : FileSafeProxy
    {
        $fileSafe = clone $this->getFileSafe();

        return new FileSafeProxy($fileSafe);
    }

    public function fileSafe() : FileSafeProxy
    {
        $fileSafe = $this->getFileSafe();

        return new FileSafeProxy($fileSafe);
    }

    protected function createFileSafe() : FileSafe
    {
        return new FileSafe();
    }

    protected function getFileSafe() : FileSafe
    {
        return $this->fileSafe = $this->fileSafe ?? $this->createFileSafe();
    }


    public function newSocketSafe() : SocketSafeProxy
    {
        $socketSafe = $this->createSocketSafe();

        return new SocketSafeProxy($socketSafe);
    }

    public function cloneSocketSafe() : SocketSafeProxy
    {
        $socketSafe = clone $this->getSocketSafe();

        return new SocketSafeProxy($socketSafe);
    }

    public function socketSafe() : SocketSafeProxy
    {
        $socketSafe = $this->getSocketSafe();

        return new SocketSafeProxy($socketSafe);
    }

    protected function createSocketSafe() : SocketSafe
    {
        return new SocketSafe();
    }

    protected function getSocketSafe() : SocketSafe
    {
        return $this->socketSafe = $this->socketSafe ?? $this->createSocketSafe();
    }


    public function newStreamSafe() : StreamSafeProxy
    {
        $streamSafe = $this->createStreamSafe();

        return new StreamSafeProxy($streamSafe);
    }

    public function cloneStreamSafe() : StreamSafeProxy
    {
        $streamSafe = clone $this->getStreamSafe();

        return new StreamSafeProxy($streamSafe);
    }

    public function streamSafe() : StreamSafeProxy
    {
        $streamSafe = $this->getStreamSafe();

        return new StreamSafeProxy($streamSafe);
    }

    protected function createStreamSafe() : StreamSafe
    {
        return new StreamSafe();
    }

    protected function getStreamSafe() : StreamSafe
    {
        return $this->streamSafe = $this->streamSafe ?? $this->createStreamSafe();
    }


    /**
     * @param string $value
     *
     * @return Ret<int>
     */
    public function type_chmod($value)
    {
        if (is_int($value)) {
            $int = $value;

        } elseif (is_string($value)) {
            if ('0' === $value) {
                return Ret::val(0);
            }

            $valueString = ltrim($value, '0');
            if ('' === $valueString) {
                return Ret::err(
                    [ 'The `value` should be string, that contains numbers except zero', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if (! preg_match($regex = '/^[0124]?[0-7]{3}$/', $valueString)) {
                return Ret::err(
                    [ 'The `value` should be string, that match regex: ' . $regex, $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $int = octdec($valueString);

            if (0 === $int) {
                return Ret::err(
                    [ 'The `value` should be valid octal number', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

        } else {
            return Ret::err(
                [ 'The `value` should be integer or string', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (($int < 0) || ($int > 04777)) {
            return Ret::err(
                [ 'The `value` should be integer from 0 to 04777', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        $octString = decoct($int);

        if (! in_array($octString[ 0 ], [ '0', '1', '2', '4' ], true)) {
            return Ret::err(
                [ 'The `value` should be string that starts from [0124]', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($int);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_path(
        $value,
        array $refs = []
    )
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $withPathInfo = array_key_exists(0, $refs);
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
        }
        $refPathInfo = null;

        if (! $theType
            ->string_not_empty($value)
            ->isOk([ &$valueStringNotEmpty, &$ret ])
        ) {
            return $ret;
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = $thePhp->pathinfo($valueStringNotEmpty);
            }
            catch ( \Throwable $e ) {
                return Ret::err(
                    [ 'The `value` should be valid path', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::val($valueStringNotEmpty);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_realpath(
        $value,
        ?bool $isAllowSymlink = null,
        array $refs = []
    )
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $isAllowSymlink = $isAllowSymlink ?? true;

        $withPathInfo = array_key_exists(0, $refs);
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
        }
        $refPathInfo = null;

        if (! $theType
            ->string_not_empty($value)
            ->isOk([ &$valueStringNotEmpty, &$ret ])
        ) {
            return $ret;
        }

        if (! $isAllowSymlink) {
            if (is_link($valueStringNotEmpty)) {
                return Ret::err(
                    [ 'The `value` should not be symlink', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        $realpath = realpath($valueStringNotEmpty);
        if (false === $realpath) {
            return Ret::err(
                [ 'The `value` should be valid realpath', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = $thePhp->pathinfo($realpath);
            }
            catch ( \Throwable $e ) {
                return Ret::err(
                    [ 'The `value` should be valid realpath', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::val($realpath);
    }

    /**
     * @return Ret<string>
     */
    public function type_freepath(
        $value,
        array $refs = []
    )
    {
        if (! $this->type_path($value, $refs)->isOk([ &$valuePath, &$ret ])) {
            return $ret;
        }

        if (file_exists($valuePath)) {
            return Ret::err(
                [ 'The `value` should be existing file', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valuePath);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_dirpath(
        $value,
        ?bool $isAllowExists, ?bool $isAllowSymlink = null,
        array $refs = []
    )
    {
        $isAllowExists = $isAllowExists ?? true;
        $isAllowSymlink = $isAllowSymlink ?? true;

        if (! $this->type_path($value, $refs)->isOk([ &$valuePath, &$ret ])) {
            return $ret;
        }

        $exists = file_exists($valuePath);

        if (! $isAllowExists) {
            if ($exists) {
                return Ret::err(
                    [ 'The `value` should not be existing filenode', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::val($valuePath);
        }

        if ($exists) {
            if (! is_dir($valuePath)) {
                return Ret::err(
                    [ 'The `value` should be existing directory', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            if (! $isAllowSymlink) {
                if (is_link($valuePath)) {
                    return Ret::err(
                        [ 'The `value` should not be symlink', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            $valueRealpath = realpath($valuePath);
            if (false === $valueRealpath) {
                return Ret::err(
                    [ 'The `value` should be valid realpath', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $valuePath = $valueRealpath;
        }

        return Ret::val($valuePath);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_dirpath_realpath(
        $value,
        ?bool $isAllowSymlink = null,
        array $refs = []
    )
    {
        $isAllowSymlink = $isAllowSymlink ?? true;

        if (! $this->type_realpath($value, $isAllowSymlink, $refs)->isOk([ &$valueRealpath, &$ret ])) {
            return $ret;
        }

        if (! $isAllowSymlink) {
            if (is_link($valueRealpath)) {
                return Ret::err(
                    [ 'The `value` should not be symlink', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if (! is_dir($valueRealpath)) {
            return Ret::err(
                [ 'The `value` should be existing directory', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueRealpath);
    }


    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_filepath(
        $value,
        ?bool $isAllowExists, ?bool $isAllowSymlink = null,
        array $refs = []
    )
    {
        $isAllowExists = $isAllowExists ?? true;
        $isAllowSymlink = $isAllowSymlink ?? true;

        if (! $this->type_path($value, $refs)->isOk([ &$valuePath, &$ret ])) {
            return $ret;
        }

        $exists = file_exists($valuePath);

        if (! $isAllowExists) {
            if ($exists) {
                return Ret::err(
                    [ 'The `value` should not be existing filenode', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            return Ret::val($valuePath);
        }

        if ($exists) {
            if (! $isAllowSymlink) {
                if (is_link($valuePath)) {
                    return Ret::err(
                        [ 'The `value` should not be symlink', $value ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            if (! is_file($valuePath)) {
                return Ret::err(
                    [ 'The `value` should be existing file', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $valueRealpath = realpath($valuePath);
            if (false === $valueRealpath) {
                return Ret::err(
                    [ 'The `value` should be valid realpath', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $valuePath = $valueRealpath;
        }

        return Ret::val($valuePath);
    }

    /**
     * @param array{ 0: array|null } $refs
     *
     * @return Ret<string>
     */
    public function type_filepath_realpath(
        $value,
        ?bool $isAllowSymlink = null,
        array $refs = []
    )
    {
        if (! $this->type_realpath($value, $isAllowSymlink, $refs)->isOk([ &$valueRealpath, &$ret ])) {
            return $ret;
        }

        if (! $isAllowSymlink) {
            if (is_link($value)) {
                return Ret::err(
                    [ 'The `value` should not be symlink', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if (! is_file($valueRealpath)) {
            return Ret::err(
                [ 'The `value` should be existing file', $value ],
                [ __FILE__, __LINE__ ]
            );
        }

        return Ret::val($valueRealpath);
    }


    /**
     * @return Ret<string>
     */
    public function type_filename($value)
    {
        $theType = Lib::type();

        if (! $theType->string_not_empty($value)->isOk([ &$valueStringNotEmpty, &$ret ])) {
            return $ret;
        }

        $forbidden = [ "/", "\\", DIRECTORY_SEPARATOR ];

        foreach ( $forbidden as $f ) {
            if (false !== strpos($valueStringNotEmpty, $f)) {
                return Ret::err(
                    [ 'The `value` should not contain directory separators', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        return Ret::val($valueStringNotEmpty);
    }


    /**
     * @return Ret<\SplFileInfo>
     */
    public function type_file(
        $value,
        ?array $extensions = null,
        ?array $mimeTypes = null,
        ?array $filters = null
    )
    {
        if ($value instanceof \SplFileInfo) {
            $splFileInfo = $value;

        } else {
            if (! $this->type_filepath_realpath($value)->isOk([ &$valueFilepathRealpath, &$ret ])) {
                return $ret;
            }

            try {
                $splFileInfo = new \SplFileInfo($valueFilepathRealpath);
            }
            catch ( \Throwable $e ) {
                return Ret::err(
                    [ 'The `value` should be valid file', $value ],
                    [ __FILE__, __LINE__ ]
                );
            }
        }

        if (null !== $extensions) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->_type_file_extensions($splFileInfo, $extensions);

                if (null === $splFileInfo) {
                    return Ret::err(
                        [ 'The `value` should be file, that passes extension checks', $value, $extensions ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }
        }

        if (null !== $mimeTypes) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->_type_file_mime_types($splFileInfo, $mimeTypes);

                if (null === $splFileInfo) {
                    return Ret::err(
                        [ 'The `value` should be file, that passes mime-type checks', $value, $mimeTypes ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }
        }

        if (null !== $filters) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->_type_file_filters($splFileInfo, $filters);

                if (null === $splFileInfo) {
                    return Ret::err(
                        [ 'The `value` should be file, that passes filter checks', $value, $filters ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }
        }

        return Ret::val($splFileInfo);
    }

    protected function _type_file_extensions(\SplFileInfo $splFileInfo, array $extensions) : ?\SplFileInfo
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

    protected function _type_file_mime_types(\SplFileInfo $splFileInfo, array $mimeTypes) : ?\SplFileInfo
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

    protected function _type_file_filters(\SplFileInfo $splFileInfo, array $filters) : ?\SplFileInfo
    {
        if ([] === $filters) {
            return $splFileInfo;
        }

        $theFormat = Lib::format();

        $filtersList = [
            'max_size' => true,
            'min_size' => true,
        ];

        $filtersIntersect = array_intersect_key($filters, $filtersList);

        if ([] !== $filtersIntersect) {
            $hasMaxSize = array_key_exists('max_size', $filters);
            $hasMinSize = array_key_exists('min_size', $filters);

            $fileSize = null;
            if ($hasMaxSize || $hasMinSize) {
                $fileSize = $splFileInfo->getSize();
            }

            foreach ( $filters as $filter => $value ) {
                if ('max_size' === $filter) {
                    $maxSize = $theFormat->bytes_decode([ NAN ], $value);

                    if (! ($fileSize <= $maxSize)) {
                        return null;
                    }

                } elseif ('min_size' === $filter) {
                    $minSize = $theFormat->bytes_decode([ NAN ], $value);

                    if (! ($fileSize >= $minSize)) {
                        return null;
                    }
                }
            }
        }

        return $splFileInfo;
    }


    /**
     * @return Ret<\SplFileInfo>
     */
    public function type_image(
        $value,
        ?array $extensions = null,
        ?array $mimeTypes = null,
        ?array $filters = null
    )
    {
        if (! $this->type_file($value, $extensions, $mimeTypes, $filters)->isOk([ &$splFileInfo, &$ret ])) {
            return $ret;
        }

        if (null !== $filters) {
            if (null !== $splFileInfo) {
                $splFileInfo = $this->_type_image_filters($splFileInfo, $filters);

                if (null === $splFileInfo) {
                    return Ret::err(
                        [ 'The `value` should be image, that passes image filter checks', $value, $filters ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }
        }

        return Ret::val($splFileInfo);
    }

    protected function _type_image_filters(\SplFileInfo $splFileInfo, array $filters) : ?\SplFileInfo
    {
        if ([] === $filters) {
            return $splFileInfo;
        }

        $theType = Lib::type();

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

            foreach ( $filters as $filter => $value ) {
                if ('max_width' === $filter) {
                    if (! $theType->numeric_int_positive($value)->isOk([ &$maxWidth ])) {
                        return null;
                    }

                    if (! ($imageWidth <= $maxWidth)) {
                        return null;
                    }

                } elseif ('max_height' === $filter) {
                    if (! $theType->numeric_int_positive($value)->isOk([ &$maxHeight ])) {
                        return null;
                    }

                    if (! ($imageHeight <= $maxHeight)) {
                        return null;
                    }

                } elseif ('min_width' === $filter) {
                    if (! $theType->numeric_int_positive($value)->isOk([ &$minWidth ])) {
                        return null;
                    }

                    if (! ($imageWidth >= $minWidth)) {
                        return null;
                    }

                } elseif ('min_height' === $filter) {
                    if (! $theType->numeric_int_positive($value)->isOk([ &$minHeight ])) {
                        return null;
                    }

                    if (! ($imageHeight >= $minHeight)) {
                        return null;
                    }

                } elseif ('width' === $filter) {
                    if (! $theType->numeric_int_positive($value)->isOk([ &$exactWidth ])) {
                        return null;
                    }

                    if (! ($imageWidth == $exactWidth)) {
                        return null;
                    }

                } elseif ('height' === $filter) {
                    if (! $theType->numeric_int_positive($value)->isOk([ &$exactHeight ])) {
                        return null;
                    }

                    if (! ($imageHeight == $exactHeight)) {
                        return null;
                    }

                } elseif ('ratio' === $filter) {
                    if ($theType->numeric($value)->isOk([ &$ratioNumeric ])) {
                        $ratio = round($ratioNumeric, 3);

                    } elseif ($theType->string_not_empty($value)->isOk([ &$ratioStringNotEmpty ])) {
                        [ $ratioW, $ratioH ] = explode('/', $ratioStringNotEmpty) + [ 0, 0 ];

                        if (! $theType->numeric_int_positive($ratioW)->isOk([ &$ratioWNumericIntPositive ])) {
                            return null;
                        }

                        if (! $theType->numeric_int_positive($ratioH)->isOk([ &$ratioHNumericIntPositive ])) {
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


    /**
     * @return Ret<resource|\Socket>
     */
    public function type_socket($value)
    {
        $theType = Lib::type();

        if (false
            || is_a($value, '\Socket')
            || $theType->resource_opened($value, 'socket')->isOk()
        ) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be socket, opened' ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return Ret<resource>
     */
    public function type_stream($value)
    {
        $theType = Lib::type();

        if ($theType->resource_opened($value, 'stream')->isOk()) {
            return Ret::val($value);
        }

        return Ret::err(
            [ 'The `value` should be socket, opened' ],
            [ __FILE__, __LINE__ ]
        );
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
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        return $thePhp->pathinfo($path, $separatorChar, '.', $flags);
    }

    public function dirname(string $path, ?int $levels = null, ?string $separator = null) : ?string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        return $thePhp->dirname($path, $separatorChar, $levels);
    }

    public function basename(string $path, ?string $extension = null) : ?string
    {
        $thePhp = Lib::php();

        return $thePhp->basename($path, $extension);
    }

    public function filename(string $path) : ?string
    {
        $thePhp = Lib::php();

        return $thePhp->filename($path, '.');
    }

    public function fname(string $path) : ?string
    {
        $thePhp = Lib::php();

        return $thePhp->fname($path, '.');
    }

    public function extension(string $path) : ?string
    {
        $thePhp = Lib::php();

        return $thePhp->extension($path, '.');
    }

    public function extensions(string $path) : ?string
    {
        $thePhp = Lib::php();

        return $thePhp->extensions($path, '.');
    }


    public function path_normalize(string $path, ?string $separator = null) : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        if ($theType->realpath($path)->isOk([ &$pathRealpath ])) {
            $pathNormalized = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $pathRealpath);

        } else {
            $pathNormalized = $thePhp->path_normalize($path, $separatorChar);
        }

        return $pathNormalized;
    }

    public function path_resolve(string $path, ?string $separator = null) : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        if ($theType->realpath($path)->isOk([ &$pathRealpath ])) {
            $pathResolved = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $pathRealpath);

        } else {
            $pathResolved = $thePhp->path_resolve($path, $separatorChar, '.');
        }

        return $pathResolved;
    }


    public function path_relative(
        string $path, string $root,
        ?string $separator = null
    ) : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        if ($theType->realpath($path)->isOk([ &$pathRealpath ])) {
            $path = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $pathRealpath);
        }

        if ($theType->realpath($root)->isOk([ &$rootRealpath ])) {
            $root = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $rootRealpath);
        }

        $pathRelative = $thePhp->path_relative($path, $root, $separatorChar, '.');

        return $pathRelative;
    }

    public function path_absolute(
        string $path, string $current,
        ?string $separator = null
    ) : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        if ($theType->realpath($path)->isOk([ &$pathRealpath ])) {
            $path = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $pathRealpath);
        }

        if ($theType->realpath($current)->isOk([ &$currentRealpath ])) {
            $current = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $currentRealpath);
        }

        $pathAbsolute = $thePhp->path_absolute($path, $current, $separatorChar, '.');

        return $pathAbsolute;
    }

    public function path_or_absolute(
        string $path, string $current,
        ?string $separator = null
    ) : string
    {
        $thePhp = Lib::php();
        $theType = Lib::type();

        $separatorChar = $theType->char($separator ?? DIRECTORY_SEPARATOR)->orThrow();

        if ($this->type_realpath($path)->isOk([ &$pathRealpath ])) {
            $path = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $pathRealpath);
        }

        if ($this->type_realpath($current)->isOk([ &$currentRealpath ])) {
            $current = str_replace(DIRECTORY_SEPARATOR, $separatorChar, $currentRealpath);
        }

        $pathOrAbsolute = $thePhp->path_or_absolute($path, $current, $separatorChar, '.');

        return $pathOrAbsolute;
    }


    public function lpush(
        string $file, string $data
    ) : bool
    {
        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileInLock = "{$fileFreepath}.in.lock";

        $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $fileIn, $fileInLock, $data
            ) {
                if ($fhInLock = $theFsFile->fopen_flock_tmpfile(
                    $fileInLock, 'w', LOCK_EX | LOCK_NB,
                )) {
                    fwrite($fhInLock, getmypid());

                    $line = base64_encode($data);

                    file_put_contents($fileIn, $line . "\n" . file_get_contents($fileIn));
                }
            }
        );

        return true;
    }

    public function blpush(
        $tickUsleep, $timeoutMs,
        string $file, string $data
    ) : bool
    {
        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileInLock = "{$fileFreepath}.in.lock";

        $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $tickUsleep, $timeoutMs,
                $fileIn, $fileInLock, $data
            ) {
                if ($fhInLock = $theFsFile->fopen_flock_tmpfile_pooling(
                    $tickUsleep, $timeoutMs,
                    $fileInLock, 'w', LOCK_EX | LOCK_NB
                )) {
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
        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileInLock = "{$fileFreepath}.in.lock";

        $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $fileIn, $fileInLock, $data
            ) {
                if ($fhInLock = $theFsFile->fopen_flock_tmpfile(
                    $fileInLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    fwrite($fhInLock, getmypid());

                    $line = base64_encode($data);

                    file_put_contents($fileIn, $line . "\n", FILE_APPEND);
                }
            }
        );

        return true;
    }

    public function brpush(
        $tickUsleep, $timeoutMs,
        string $file, string $data
    ) : bool
    {
        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileInLock = "{$fileFreepath}.in.lock";

        $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $tickUsleep, $timeoutMs,
                $fileIn, $fileInLock, $data
            ) {
                if ($fhInLock = $theFsFile->fopen_flock_tmpfile_pooling(
                    $tickUsleep, $timeoutMs,
                    $fileInLock, 'w', LOCK_EX | LOCK_NB
                )) {
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

        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileOut = "{$fileFreepath}.lpop";
        $fileOutLock = "{$fileFreepath}.lpop.lock";

        $data = $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $fileIn, $fileOut, $fileOutLock
            ) {
                $data = null;

                if ($fhOutLock = $theFsFile->fopen_flock_tmpfile(
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
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
                        if ($fhOut = $theFsFile->fopen($fileOut, 'rb+')) {
                            $line = fgets($fhOut);
                            $rest = stream_get_contents($fhOut);

                            rewind($fhOut);
                            ftruncate($fhOut, 0);

                            if ('' !== $rest) {
                                fwrite($fhOut, $rest);
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

        if ($deleteIfEmpty) {
            if (is_file($fileIn) && ! filesize($fileIn)) {
                unlink($fileIn);
            }

            if (is_file($fileOut) && ! filesize($fileOut)) {
                unlink($fileOut);
            }
        }

        return $data;
    }

    public function blpop(
        $blockTickUsleep, $blockTimeoutMs,
        string $file, ?bool $deleteIfEmpty = null
    ) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $thePhp = Lib::php();
        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileOut = "{$fileFreepath}.lpop";
        $fileOutLock = "{$fileFreepath}.lpop.lock";

        $data = $theFsFile->call_safe(
            static function () use (
                $theFsFile, $thePhp,
                $blockTickUsleep, $blockTimeoutMs,
                $fileIn, $fileOut, $fileOutLock
            ) {
                $data = null;

                if ($fhOutLock = $theFsFile->fopen_flock_tmpfile_pooling(
                    $blockTickUsleep, $blockTimeoutMs,
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    fwrite($fhOutLock, getmypid());

                    $data = $thePhp->pooling_sync(
                        $blockTickUsleep, $blockTimeoutMs,
                        //
                        static function ($ctx) use (
                            $theFsFile,
                            $fileIn, $fileOut,
                            //
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
                                    $fhOut = $theFsFile->fopen($fileOut, 'rb+');
                                }

                                if ($fhOut) {
                                    $line = fgets($fhOut);
                                    $rest = stream_get_contents($fhOut);

                                    rewind($fhOut);
                                    ftruncate($fhOut, 0);

                                    if ('' !== $rest) {
                                        fwrite($fhOut, $rest);
                                    }

                                    $line = rtrim($line);

                                    if ('' !== $line) {
                                        $data = base64_decode($line);

                                        $ctx->setResult($data);
                                    }
                                }
                            }
                        }
                    );

                    if (false === $data) {
                        $data = null;
                    }
                }

                return $data;
            }
        );

        if ($deleteIfEmpty) {
            if (is_file($fileIn) && ! filesize($fileIn)) {
                unlink($fileIn);
            }

            if (is_file($fileOut) && ! filesize($fileOut)) {
                unlink($fileOut);
            }
        }

        return $data;
    }


    public function rpop(string $file, ?bool $deleteIfEmpty = null) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileOut = "{$fileFreepath}.rpop";
        $fileOutLock = "{$fileFreepath}.rpop.lock";

        $data = $theFsFile->call_safe(
            static function () use (
                $theFsFile,
                $fileIn, $fileOut, $fileOutLock
            ) {
                $data = null;

                if ($fhOutLock = $theFsFile->fopen_flock_tmpfile(
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    fwrite($fhOutLock, getmypid());

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
                        if ($fhOut = $theFsFile->fopen($fileOut, 'rb+')) {
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

        if ($deleteIfEmpty) {
            if (is_file($fileIn) && ! filesize($fileIn)) {
                unlink($fileIn);
            }
            if (is_file($fileOut) && ! filesize($fileOut)) {
                unlink($fileOut);
            }
        }

        return $data;
    }

    public function brpop(
        $blockTickUsleep, $blockTimeoutMs,
        string $file, ?bool $deleteIfEmpty = null
    ) : ?string
    {
        $deleteIfEmpty = $deleteIfEmpty ?? false;

        $thePhp = Lib::php();
        $theFsFile = $this->fileSafe();

        $fileFreepath = $this->type_freepath($file)->orThrow();

        $fileDir = dirname($fileFreepath);

        $this->type_dirpath_realpath($fileDir)->orThrow();

        $fileIn = "{$fileFreepath}.in";
        $fileOut = "{$fileFreepath}.rpop";
        $fileOutLock = "{$fileFreepath}.rpop.lock";

        $data = $theFsFile->call_safe(
            static function () use (
                $theFsFile, $thePhp,
                $blockTickUsleep, $blockTimeoutMs,
                $fileIn, $fileOut, $fileOutLock
            ) {
                $data = null;

                if ($fhOutLock = $theFsFile->fopen_flock_pooling(
                    $blockTickUsleep, $blockTimeoutMs,
                    $fileOutLock, 'w', LOCK_EX | LOCK_NB
                )) {
                    fwrite($fhOutLock, getmypid());

                    $data = $thePhp->pooling_sync(
                        $blockTickUsleep, $blockTimeoutMs,
                        //
                        static function ($ctx) use (
                            $theFsFile,
                            $fileIn, $fileOut,
                            //
                            &$fhOut
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
                                    $fhOut = $theFsFile->fopen($fileOut, 'rb+');
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

                                        $ctx->setResult($data);
                                    }
                                }
                            }
                        }
                    );

                    if (false === $data) {
                        $data = null;
                    }
                }

                return $data;
            }
        );

        if ($deleteIfEmpty) {
            if (is_file($fileIn) && ! filesize($fileIn)) {
                unlink($fileIn);
            }
            if (is_file($fileOut) && ! filesize($fileOut)) {
                unlink($fileOut);
            }
        }

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

        $dirpathRealpath = $this->type_dirpath_realpath($dirpath)->orThrow();

        $it = new \RecursiveDirectoryIterator(
            $dirpathRealpath,
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

        $theType = Lib::type();

        $lengthInt = $theType->int_positive($length)->orThrow();
        $lengthBufferInt = $theType->int_positive($lengthBuffer)->orThrow();

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
            $chunk1 = fread($fh1, $lengthInt);
            $chunk2 = fread($fh2, $lengthInt);

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
                                $buffer2 = fgets($fh2, $lengthBufferInt);

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
                                $buffer1 = fgets($fh1, $lengthBufferInt);

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
                            while ( false
                                || ($hasFh1 = $hasFh1 && (! feof($fh1)))
                                || ($hasFh2 = $hasFh2 && (! feof($fh2)))
                            ) {
                                $buffer1 = fgets($fh1, $lengthBufferInt);
                                $buffer2 = fgets($fh2, $lengthBufferInt);

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
            $len = file_put_contents($filepath, '');

            if (false === $len) {
                throw new LogicException(
                    [ 'Unable to create file', $filepath ]
                );
            }

            clearstatcache(true, $filepath);
        }

        $filepathRealpath = realpath($filepath);

        if (false === $filepathRealpath) {
            throw new LogicException(
                [ 'Unable to realpath', $filepath ]
            );
        }

        $startTrim = $theType->trim($start)->orThrow();

        if (null !== ($endTrim = $end)) {
            $endTrim = $theType->trim($end)->orThrow();
        }

        if ($startTrim === $endTrim) {
            throw new LogicException(
                [ 'The `start` should not be equal to `end`', $startTrim, $endTrim ]
            );
        }

        $input = fopen($filepathRealpath, 'rb');
        if (false === $input) {
            throw new FilesystemException(
                [ 'Unable to perform fopen() on file', $filepathRealpath ]
            );
        }

        $filepathRealpathTmp = $filepathRealpath . '.tmp';

        $output = fopen($filepathRealpathTmp, 'wb');
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
