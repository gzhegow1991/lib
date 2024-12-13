<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\RuntimeException;


trait FsTrait
{
    public static function fs_file_get_contents(
        string $filepath,
        bool $use_include_path = null, $context = null, int $offset = null, int $length = null
    ) : ?string
    {
        $use_include_path = $use_include_path ?? false;
        $offset = $offset ?? 0;

        if (null === ($_filepath = static::parse_filepath_realpath($filepath))) {
            throw new RuntimeException(
                'File not found: ' . $filepath
            );
        }

        $args = [];
        if (null !== $length) {
            $args[] = $length;
        }

        $result = file_get_contents(
            $_filepath,
            $use_include_path,
            $context,
            $offset,
            ...$args
        );

        if (false === $result) {
            throw new RuntimeException(
                'Unable to read file: ' . $filepath
            );
        }

        return $result;
    }

    public static function fs_file_put_contents(
        string $filepath, $data, int $flags = null,
        array $mkdirArgs = null,
        array $filePutContentsArgs = null,
        array $chmodArgs = null
    ) : ?int
    {
        $flags = $flags ?? 0;
        $mkdirArgs = $mkdirArgs ?? [ 0775, true ];
        $filePutContentsArgs = $filePutContentsArgs ?? [];
        $chmodArgs = $chmodArgs ?? [ 0664 ];

        if (null === ($_filepath = static::parse_filepath($filepath))) {
            throw new RuntimeException(
                'Bad filepath: ' . $filepath
            );
        }

        $dirpath = dirname($_filepath);

        if (! is_dir($dirpath)) {
            $status = mkdir(
                $dirpath,
                ...$mkdirArgs
            );

            if (false === $status) {
                throw new RuntimeException(
                    'Unable to mkdir: ' . $filepath
                );
            }
        }

        $result = file_put_contents(
            $filepath, $data,
            $flags,
            ...$filePutContentsArgs
        );

        if (false === $result) {
            throw new RuntimeException(
                'Unable to write file: ' . $filepath
            );
        }

        $status = chmod(
            $filepath,
            ...$chmodArgs
        );

        if (false === $status) {
            throw new RuntimeException(
                'Unable to perform chmod() on file: ' . $filepath
            );
        }

        return $result;
    }


    public static function fs_unlink(
        string $path, bool $recursive = null,
        array $rmdirArgs = null, array $unlinkArgs = null,
        array &$removed = null
    ) : bool
    {
        $removed = null;

        $recursive = $recursive ?? false;
        $rmdirArgs = $rmdirArgs ?? [];
        $unlinkArgs = $unlinkArgs ?? [];

        if (null === ($_path = static::parse_path($path))) {
            throw new RuntimeException(
                'Bad path: ' . $path
            );
        }

        if (! file_exists($_path)) {
            return true;
        }

        if (is_dir($_path)) {
            if ($recursive) {
                $status = static::fs_rmdir_recursive($_path, $rmdirArgs, $unlinkArgs, $removed);

            } else {
                $status = rmdir($_path, ...$unlinkArgs);

                $removed[ $_path ] = $status;
            }

        } else {
            $status = unlink($_path, ...$unlinkArgs);

            $removed[ $_path ] = $status;
        }

        return $status;
    }


    public static function fs_rmdir_recursive(
        string $dirpath,
        array $rmdirArgs = null, array $unlinkArgs = null,
        array &$removed = null
    ) : bool
    {
        $removed = null;

        $rmdirArgs = $rmdirArgs ?? [];
        $unlinkArgs = $unlinkArgs ?? [];

        if (null === ($_dirpath = static::parse_dirpath($dirpath))) {
            throw new RuntimeException(
                'Bad dirpath: ' . $_dirpath
            );
        }

        if (! file_exists($_dirpath)) {
            return true;
        }

        $removed = [];

        $it = new \RecursiveDirectoryIterator(
            $_dirpath,
            \FilesystemIterator::SKIP_DOTS
        );

        $iit = new \RecursiveIteratorIterator(
            $it,
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ( $iit as $fileInfo ) {
            /** @var \SplFileInfo $fileInfo */

            $realpath = $fileInfo->getRealPath();

            if ($fileInfo->isDir()) {
                $status = rmdir($realpath, ...$rmdirArgs);

            } else {
                $status = unlink($realpath, ...$unlinkArgs);
            }

            $removed[ $realpath ] = $status;

            if (! $status) {
                return false;
            }
        }

        $status = rmdir($_dirpath, ...$rmdirArgs);

        $removed[ $_dirpath ] = $status;

        if (! $status) {
            return false;
        }

        return true;
    }
}
