<?php

namespace Gzhegow\Lib\Modules;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\LogicException;
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
        &$result, $value,
        array $refs = []
    ) : bool
    {
        $result = null;

        $withPathInfo = array_key_exists(0, $refs);

        $refPathInfo = null;
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
            $refPathInfo = null;
        }

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

        $refPathInfo = null;
        if ($withPathInfo) {
            $refPathInfo =& $refs[ 0 ];
            $refPathInfo = null;
        }

        if (! Lib::type()->string_not_empty($valueString, $value)) {
            return false;
        }

        if (! $isAllowSymlink) {
            if (is_link($valueString)) {
                return false;
            }
        }

        $_value = realpath($valueString);

        if (false === $_value) {
            return false;
        }

        if ($withPathInfo) {
            try {
                $refPathInfo = Lib::php()->pathinfo($_value);
            }
            catch ( \Throwable $e ) {
                unset($refPathInfo);

                return false;
            }
        }

        $result = $_value;

        unset($refPathInfo);

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

        $isAllowExists = $isAllowExists ?? false;
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

            $_value = realpath($_value);
        }

        $result = $_value;

        return $_value;
    }

    /**
     * @param string|null            $result
     * @param array{ 0: array|null } $refs
     */
    public function type_filepath(
        &$result, $value,
        ?bool $isAllowExists = null, ?bool $isAllowSymlink = null,
        array $refs = []
    ) : bool
    {
        $result = null;

        $isAllowExists = $isAllowExists ?? false;
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
            if (! is_file($_value)) {
                return false;
            }

            if (! $isAllowSymlink) {
                if (is_link($_value)) {
                    return false;
                }
            }

            $_value = realpath($_value);
        }

        $result = $_value;

        return $_value;
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

        if (! is_dir($_value)) {
            return false;
        }

        $result = $_value;

        return $_value;
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

        if (! is_file($_value)) {
            return false;
        }

        $result = $_value;

        return $_value;
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

        foreach ( $forbidden as $f ) {
            if (false !== strpos($valueString, $f)) {
                return false;
            }
        }

        $result = $valueString;

        return true;
    }


    public function file_get_contents(
        string $filepath,
        ?array $fileGetContentsArgs = null
    ) : ?string
    {
        $fileGetContentsArgs = $fileGetContentsArgs ?: [];

        if (! $this->type_filepath_realpath($_filepath, $filepath)) {
            throw new RuntimeException(
                [ 'File not found', $filepath ]
            );
        }

        $result = file_get_contents(
            $_filepath,
            ...$fileGetContentsArgs
        );

        if (false === $result) {
            throw new RuntimeException(
                [ 'Unable to read file', $filepath ]
            );
        }

        return $result;
    }

    public function file_put_contents(
        string $filepath, $data,
        ?array $filePutContentsArgs = null,
        ?array $mkdirArgs = null,
        ?array $chmodArgs = null
    ) : ?int
    {
        $_filePutContentsArgs = $filePutContentsArgs ?? [];

        $withMkdir = (null !== $mkdirArgs);
        $withChmod = (null !== $chmodArgs);

        $_mkdirArgs = null;
        $_chmodArgs = null;
        if ($withMkdir) $_mkdirArgs = ($mkdirArgs ?: [ 0775, true ]);
        if ($withChmod) $_chmodArgs = ($chmodArgs ?: [ 0664 ]);

        if (! $this->type_filepath($_filepath, $filepath, true)) {
            throw new RuntimeException(
                [ 'Bad filepath', $filepath ]
            );
        }

        if ($withMkdir) {
            $dirpath = dirname($_filepath);

            if (! is_dir($dirpath)) {
                $status = mkdir(
                    $dirpath,
                    ...$_mkdirArgs
                );

                if (false === $status) {
                    throw new RuntimeException(
                        [ 'Unable to mkdir', $filepath ]
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
                [ 'Unable to write file', $filepath ]
            );
        }

        if ($withChmod) {
            $status = chmod(
                $filepath,
                ...$_chmodArgs
            );

            if (false === $status) {
                throw new RuntimeException(
                    [ 'Unable to perform chmod() on file', $filepath ]
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
        ?array $recursiveDirectoryIteratorArgs = null,
        ?array $recursiveIteratorIteratorArgs = null
    ) : \Generator
    {
        $recursiveDirectoryIteratorArgs = $recursiveDirectoryIteratorArgs ?: [ \FilesystemIterator::SKIP_DOTS ];
        $recursiveIteratorIteratorArgs = $recursiveIteratorIteratorArgs ?: [ \RecursiveIteratorIterator::CHILD_FIRST ];

        if (! $this->type_dirpath_realpath($_dirpath, $dirpath)) {
            throw new RuntimeException(
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


    public function rm(
        string $filepath,
        ?array $unlinkArgs = null
    ) : bool
    {
        $unlinkArgs = $unlinkArgs ?: [];

        if (! $this->type_filepath($_filepath, $filepath, true)) {
            throw new RuntimeException(
                [ 'Bad filepath', $filepath ]
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
                [ 'Unable to delete file', $filepath ]
            );
        }

        return true;
    }

    public function rmdir(
        string $dirpath,
        ?array $rmdirArgs = null
    ) : bool
    {
        $rmdirArgs = $rmdirArgs ?: [];

        if (! $this->type_dirpath($_dirpath, $dirpath, true)) {
            throw new RuntimeException(
                [ 'Bad dirpath', $_dirpath ]
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
                [ 'Unable to delete directory', $dirpath ]
            );
        }

        return true;
    }


    /**
     * > заменяет слеши в пути на указанные
     */
    public function path_normalize(string $path, ?string $separator = null) : string
    {
        $separator = Lib::parse()->char($separator) ?? DIRECTORY_SEPARATOR;

        if ($this->type_realpath($realpath, $path)) {
            $normalized = str_replace(DIRECTORY_SEPARATOR, $separator, $realpath);

        } else {
            $normalized = Lib::php()->path_normalize($path, $separator);
        }

        return $normalized;
    }

    /**
     * > разбирает последовательности `./path` и `../path` и возвращает нормализованный путь
     */
    public function path_resolve(string $path, ?string $separator = null) : string
    {
        $separator = Lib::parse()->char($separator) ?? DIRECTORY_SEPARATOR;

        if ($this->type_realpath($realpath, $path)) {
            $resolved = str_replace(DIRECTORY_SEPARATOR, $separator, $realpath);

        } else {
            $resolved = Lib::php()->path_resolve($path, $separator, '.');
        }

        return $resolved;
    }


    /**
     * > возвращает относительный нормализованный путь, отрезая у него $root
     */
    public function path_relative(
        string $path, string $root,
        ?string $separator = null
    ) : string
    {
        $separator = Lib::parse()->char($separator) ?? DIRECTORY_SEPARATOR;

        return Lib::php()->path_relative($path, $root, $separator, '.');
    }

    /**
     * > возвращает абсолютный нормализованный путь, с поддержкой `./path` и `../path`
     */
    public function path_absolute(
        string $path, string $current,
        ?string $separator = null
    ) : string
    {
        $separator = Lib::parse()->char($separator) ?? DIRECTORY_SEPARATOR;

        return Lib::php()->path_absolute($path, $current, $separator, '.');
    }

    /**
     * > возвращает абсолютный нормализованный путь, с поддержкой `./path` и `../path`, но только если путь начинается с `.`
     */
    public function path_or_absolute(
        string $path, string $current,
        ?string $separator = null
    ) : string
    {
        $separator = Lib::parse()->char($separator) ?? DIRECTORY_SEPARATOR;

        return Lib::php()->path_or_absolute($path, $current, $separator, '.');
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
            throw new RuntimeException(
                [ 'Unable to open file with flags: rb', $file1 ]
            );
        }
        if (false === $fh2) {
            throw new RuntimeException(
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
                            while ( false
                                || ($hasFh1 = $hasFh1 && (! feof($fh1)))
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
}
