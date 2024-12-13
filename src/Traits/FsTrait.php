<?php

namespace Gzhegow\Lib\Traits;

use Gzhegow\Lib\Exception\RuntimeException;


trait FsTrait
{
    public static function fs_file_get_contents(
        string $filepath,
        array $fileGetContentsArgs = null
    ) : ?string
    {
        $fileGetContentsArgs = $fileGetContentsArgs ?? [];

        if (null === ($_filepath = static::parse_filepath_realpath($filepath))) {
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

    public static function fs_file_put_contents(
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

        if (null === ($_filepath = static::parse_filepath($filepath))) {
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
    public static function fs_dir_walk(
        string $dirpath,
        array $recursiveDirectoryIteratorArgs = null,
        array $recursiveIteratorIteratorArgs = null
    ) : \Generator
    {
        $recursiveDirectoryIteratorArgs = $recursiveDirectoryIteratorArgs ?? [ \FilesystemIterator::SKIP_DOTS ];
        $recursiveIteratorIteratorArgs = $recursiveIteratorIteratorArgs ?? [ \RecursiveIteratorIterator::CHILD_FIRST ];

        if (null === ($_dirpath = static::parse_dirpath($dirpath))) {
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


    public static function fs_rm(
        string $filepath,
        array $unlinkArgs = null
    ) : bool
    {
        $unlinkArgs = $unlinkArgs ?? [];

        if (null === ($_filepath = static::parse_filepath($filepath))) {
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

    public static function fs_rmdir(
        string $dirpath,
        array $rmdirArgs = null
    ) : bool
    {
        $rmdirArgs = $rmdirArgs ?? [];

        if (null === ($_dirpath = static::parse_dirpath($dirpath))) {
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
}
