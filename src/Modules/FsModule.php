<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


class FsModule
{
    public function __construct()
    {
        if (! extension_loaded('fileinfo')) {
            throw new RuntimeException(
                'Missing PHP extension: fileinfo'
            );
        }
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_path(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        $result = null;

        $withPathInfo = array_key_exists(0, $refs);

        $refPathInfo = null;
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
            $refPathInfo = null;
        }

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        if (false !== strpos($_value, "\0")) {
            return false;
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = pathinfo($_value);
                unset($refPathInfo);
            }
            catch ( \Throwable $e ) {
                return false;
            }
        }

        $result = $_value;

        unset($refPathInfo);

        return $_value;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_dirpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        $result = null;

        $status = $this->type_path(
            $_value,
            $value, $refs
        );

        if (! $status) {
            return false;
        }

        $status = file_exists($_value);

        if (! $status) {
            // > dirpath is available
            $result = $_value;

            return true;
        }

        if (! is_dir($_value)) {
            return false;
        }

        $_value = realpath($_value);

        $result = $_value;

        return $_value;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_filepath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        $status = $this->type_path(
            $_value,
            $value, $refs
        );

        if (! $status) {
            return false;
        }

        $status = file_exists($_value);

        if (false === $status) {
            // > filepath is available
            $result = $_value;

            return true;
        }

        if (! is_file($_value)) {
            return false;
        }

        $_value = realpath($_value);

        $result = $_value;

        return $_value;
    }


    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_path_realpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        $result = null;

        $status = $this->type_path(
            $_value,
            $value, $refs
        );

        if (! $status) {
            return false;
        }

        if (false === ($_value = realpath($_value))) {
            return false;
        }

        $result = $_value;

        return true;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_dirpath_realpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        $result = null;

        $status = $this->type_path(
            $_value,
            $value, $refs
        );

        if (! $status) {
            return false;
        }

        $status = file_exists($_value);

        if (! $status) {
            return false;
        }

        if (! is_dir($_value)) {
            return false;
        }

        $_value = realpath($_value);

        $result = $_value;

        return $_value;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_filepath_realpath(
        &$result,
        $value, array $refs = []
    ) : bool
    {
        $result = null;

        $status = $this->type_path(
            $_value,
            $value, $refs
        );

        if (! $status) {
            return false;
        }

        $status = file_exists($_value);

        if (! $status) {
            return false;
        }

        if (! is_file($_value)) {
            return false;
        }

        $_value = realpath($_value);

        $result = $_value;

        return true;
    }


    /**
     * @param string|null $result
     */
    public function type_filename(&$result, $value) : bool
    {
        $result = null;

        if (! Lib::type()->string_not_empty($_value, $value)) {
            return false;
        }

        $forbidden = [ "\0", "/", "\\", DIRECTORY_SEPARATOR ];

        foreach ( $forbidden as $f ) {
            if (false !== strpos($_value, $f)) {
                return false;
            }
        }

        $result = $_value;

        return true;
    }


    public function file_get_contents(
        string $filepath,
        array $fileGetContentsArgs = null
    ) : ?string
    {
        $fileGetContentsArgs = $fileGetContentsArgs ?? [];

        if (null === ($_filepath = Lib::parse()->filepath_realpath($filepath))) {
            throw new RuntimeException(
                'File not found: ' . $filepath
            );
        }

        $result = file_get_contents(
            $_filepath,
            ...$fileGetContentsArgs
        );

        if (false === $result) {
            throw new RuntimeException(
                'Unable to read file: ' . $filepath
            );
        }

        return $result;
    }

    public function file_put_contents(
        string $filepath, $data,
        array $mkdirArgs = null,
        array $filePutContentsArgs = null,
        array $chmodArgs = null
    ) : ?int
    {
        $_filePutContentsArgs = $filePutContentsArgs ?? [];

        $_mkdirArgs = null;
        $_chmodArgs = null;
        if (null !== $mkdirArgs) $_mkdirArgs = ($mkdirArgs ?: [ 0775, true ]);
        if (null !== $chmodArgs) $_chmodArgs = ($chmodArgs ?: [ 0664 ]);

        if (null === ($_filepath = Lib::parse()->filepath($filepath))) {
            throw new RuntimeException(
                'Bad filepath: ' . $filepath
            );
        }

        if (null !== $_mkdirArgs) {
            $dirpath = dirname($_filepath);

            if (! is_dir($dirpath)) {
                $status = mkdir(
                    $dirpath,
                    ...$_mkdirArgs
                );

                if (false === $status) {
                    throw new RuntimeException(
                        'Unable to mkdir: ' . $filepath
                    );
                }
            }
        }

        $size = file_put_contents(
            $filepath, $data,
            ...$_filePutContentsArgs
        );

        if (false === $size) {
            throw new RuntimeException(
                'Unable to write file: ' . $filepath
            );
        }

        if (null !== $_chmodArgs) {
            $status = chmod(
                $filepath,
                ...$_chmodArgs
            );

            if (false === $status) {
                throw new RuntimeException(
                    'Unable to perform chmod() on file: ' . $filepath
                );
            }
        }

        return $size;
    }


    /**
     * @param string     $dirpath
     * @param array|null $recursiveDirectoryIteratorArgs
     * @param array|null $recursiveIteratorIteratorArgs
     *
     * @return \Generator<\SplFileInfo>
     */
    public function dir_walk_it(
        string $dirpath,
        array $recursiveDirectoryIteratorArgs = null,
        array $recursiveIteratorIteratorArgs = null
    ) : \Generator
    {
        $recursiveDirectoryIteratorArgs = $recursiveDirectoryIteratorArgs ?? [ \FilesystemIterator::SKIP_DOTS ];
        $recursiveIteratorIteratorArgs = $recursiveIteratorIteratorArgs ?? [ \RecursiveIteratorIterator::CHILD_FIRST ];

        if (null === ($_dirpath = Lib::parse()->dirpath($dirpath))) {
            throw new RuntimeException(
                'Bad dirpath: ' . $_dirpath
            );
        }

        if (! file_exists($_dirpath)) {
            return true;
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


    public function rm(
        string $filepath,
        array $unlinkArgs = null
    ) : bool
    {
        $unlinkArgs = $unlinkArgs ?? [];

        if (null === ($_filepath = Lib::parse()->filepath($filepath))) {
            throw new RuntimeException(
                'Bad filepath: ' . $_filepath
            );
        }

        if (! file_exists($_filepath)) {
            return true;
        }

        $status = unlink(
            $_filepath,
            ...$unlinkArgs
        );

        if (false === $status) {
            throw new RuntimeException(
                'Unable to delete file: ' . $filepath
            );
        }

        return true;
    }

    public function rmdir(
        string $dirpath,
        array $rmdirArgs = null
    ) : bool
    {
        $rmdirArgs = $rmdirArgs ?? [];

        if (null === ($_dirpath = Lib::parse()->dirpath($dirpath))) {
            throw new RuntimeException(
                'Bad dirpath: ' . $_dirpath
            );
        }

        if (! file_exists($_dirpath)) {
            return true;
        }

        $status = rmdir(
            $_dirpath,
            ...$rmdirArgs
        );

        if (false === $status) {
            throw new RuntimeException(
                'Unable to delete directory: ' . $dirpath
            );
        }

        return true;
    }


    /**
     * > по умолчанию для двойного расширения будет возвращено только последнее
     */
    public function pathinfo(string $path, int $flags = null) // : string|array
    {
        if (! isset($flags)) {
            $basename = basename($path);
            [ $filename, $extension ] = explode('.', $basename, 2) + [ null, null ];

            $pi = pathinfo($path);
            $pi[ 'filename' ] = $filename;
            $pi[ 'extension' ] = $extension;

            return $pi;
        }

        if ($flags & PATHINFO_EXTENSION) {
            $basename = basename($path);
            [ , $extension ] = explode('.', $basename, 2) + [ null, null ];

            return $extension;

        } elseif ($flags & PATHINFO_FILENAME) {
            $basename = basename($path);
            [ $filename, ] = explode('.', $basename, 2) + [ null, null ];

            return $filename;
        }

        return pathinfo($path, $flags);
    }


    /**
     * > разбирает последовательности /../ в пути до файла и возвращает путь через правый слеш
     */
    public function normalize(string $path) : string
    {
        if (null !== ($_path = Lib::parse()->path_realpath($path))) {
            $_path = str_replace(DIRECTORY_SEPARATOR, '/', $_path);

        } else {
            $_path = $path;

            $root = ($_path[ 0 ] === '/') ? '/' : '';

            $segments = explode('/', trim($_path, '/'));

            $ret = [];
            foreach ( $segments as $segment ) {
                if ((! $segment) || ($segment == '.')) {
                    continue;
                }

                ($segment == '..')
                    ? array_pop($ret)
                    : ($ret[] = $segment);
            }

            $_path = $root . implode('/', $ret);
        }

        return $_path;
    }

    /**
     * > возвращает относительный путь до файла
     */
    public function relative(string $path, string $root) : string
    {
        $_path = $this->normalize($path);
        $_root = $this->normalize($root);

        if ($_path === $_root) {
            throw new \RuntimeException('Path is equal to root: ' . $root);
        }

        if (0 !== strpos($_path, $_root)) {
            throw new \RuntimeException('Path is not a child of root: ' . implode(' / ', [ $root, $path ]));
        }

        $result = substr($_path, mb_strlen($_root));
        $result = ltrim($result, '/');

        return $result;
    }
}
