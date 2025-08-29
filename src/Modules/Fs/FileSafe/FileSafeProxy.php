<?php

namespace Gzhegow\Lib\Modules\Fs\FileSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @method int umask(int $umask)
 *
 * @method resource|false fopen(string $file, string $modeOpen, array $fopenArgs = [])
 * @method resource|bool fclose(resource $resource)
 * @method resource|bool fclosef(resource $resource)
 *
 * @method resource|false flock(resource $resource, int $modeLock, array $flockArgs = [])
 * @method resource|false flock_pooling(int|null $tickUsleep, int|null $timeoutMs, resource $resource, int $modeLock, array $flockArgs = [])
 * @method resource|false frelease(resource $resource)
 * @method resource|false freleasef(resource $resource)
 *
 * @method resource|false fopen_flock(string $file, string $modeOpen, int $modeLock, array $fopenArgs = [], array $flockArgs = [])
 * @method resource|false fopen_flock_pooling(int|null $tickUsleep, int|null $timeoutMs, string $file, string $modeOpen, int $modeLock, array $fopenArgs = [], array $flockArgs = [])
 * @method resource|false frelease_fclose(resource $resource)
 * @method resource|false freleasef_fclosef(resource $resource)
 *
 * @method resource|false fopen_flock_tmpfile(string $file, string $modeOpen, int $modeLock, array $fopenArgs = [], array $flockArgs = [])
 * @method resource|false fopen_flock_tmpfile_pooling(int|null $tickUsleep, int|null $timeoutMs, string $file, string $modeOpen, int $modeLock, array $fopenArgs = [], array $flockArgs = [])
 * @method resource|false frelease_fclose_unlink(resource $resource, string $file)
 * @method resource|false freleasef_fclosef_unlinkf(resource $resource, string $file)
 *
 * @method bool feof(resource $resource)
 * @method int|false ftell(resource $resource)
 * @method int|false fseek(resource $resource, int $offset, $whence = null)
 * @method resource|false rewind(resource $resource)
 *
 * @method string|false fread(resource $resource, int $length)
 * @method int|false fwrite(resource $resource, string $data, int|null $length)
 * @method int|false fwrite_fflush(resource $resource, string $data, int|null $length = null)
 *
 * @method bool ftruncate(resource $resource, int $pos)
 *
 * @method string|false fgetc(resource $resource)
 * @method string|false fgets(resource $resource, int|null $length)
 *
 * @method int|false fputs(resource $resource, string $data, int $length)
 * @method int|false fputs_fflush(resource $resource, string $data, int|null $length = null)
 *
 * @method int|false fpassthru(resource $resource)
 * @method int|false fpassthru_fflush(resource $resource)
 *
 * @method bool fflush(resource $resource)
 * @method bool fsync(resource $resource)
 * @method bool fdatasync(resource $resource)
 *
 * @method string|false filetype(string $filename)
 *
 * @method string|false file_exists(string $file)
 * @method string|false is_exists(string $file)
 * @method string|false is_file(string $file)
 * @method string|false is_realfile(string $file)
 * @method string|false is_dir(string $directory)
 * @method string|false is_realdir(string $directory)
 *
 * @method string|false is_link(string $file)
 * @method string|false is_link_target(string $file)
 * @method string|false is_link_file(string $file)
 * @method string|false is_link_dir(string $directory)
 *
 * @method string|false is_readable(string $file)
 * @method string|false is_writable(string $file)
 * @method string|false is_writeable(string $file)
 * @method string|false is_executable(string $file)
 *
 * @method bool clearstatcache(bool|null $clearRealpathCache = null, string|null $file = null)
 * @method array|false fstat(string $file)
 * @method array|false lstat(string $file)
 * @method array|false stat(resource $resource)
 *
 * @method string|false readlink(string $file)
 * @method string|false realpath(string $file, bool|null $returnTargetPath = null)
 * @method string|false realpath_target(string $file)
 * @method string|false realpath_link(string $file)
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
 * @method string|false chmod(string $file, int|string $permissions)
 * @method string|false chown(string $file, int|string $user)
 * @method string|false lchown(string $file, int|string $user)
 * @method string|false chgrp(string $file, int|string $group)
 * @method string|false lchgrp(string $file, int|string $group)
 *
 * @method string|false touch(string $file, int|null $mtime = null, int|null $atime = null)
 * @method string|false copy(string $from, string $to, $context = null)
 * @method string|false rename(string $from, string $to, $context = null)
 *
 * @method string|false unlink(string $file, $context = null)
 *
 * @method string|false rm(string $file, $context = null)
 * @method string|false rmf(string $file, $context = null)
 *
 * @method string|false mkdir(string $directory, int|null $permissions = null, bool|null $recursive = null, $context = null)
 * @method string|false mkdirp(string $directory, int|null $permissions = null, bool|null $recursive = null, $context = null)
 * @method string|false rmdir(string $directory, $context = null)
 * @method string|false rmdirf(string $directory, $context = null)
 *
 * @method string|false link(string $target, string $link)
 * @method string|false symlink(string $target, string $link)
 *
 * @method string|false file_read(string $file, string $modeOpen, array $fopenArgs = [], array $streamGetContentsArgs = [])
 * @method string|false file_read_flock(string $file, string $modeOpen, int $modeLock, array $fopenArgs = [], array $flockArgs = [], array $streamGetContentsArgs = [])
 * @method string|false file_read_flock_pooling(int|null $tickUsleep, int|null $timeoutMs, string $file, string $modeOpen, int $modeLock, array $fopenArgs = [], array $flockArgs = [], array $streamGetContentsArgs = [])
 *
 * @method int|false file_write(string $file, string $modeOpen, $data, array $fopenArgs = [])
 * @method int|false file_write_flock(string $file, string $modeOpen, int $modeLock, $data, array $fopenArgs = [], array $flockArgs = [])
 * @method int|false file_write_flock_pooling(int|null $tickUsleep, int|null $timeoutMs, string $file, string $modeOpen, int $modeLock, $data, array $fopenArgs = [], array $flockArgs = [])
 *
 * @method string[]|false file_lines(string $file, int|null $flags = null, array $fopenArgs = [])
 * @method string[]|false file_lines_flock(string $file, int $modeLock, int|null $flags = null, array $fopenArgs = [], array $flockArgs = [])
 * @method string[]|false file_lines_flock_pooling(int|null $tickUsleep, int|null $timeoutMs, string $file, int $modeLock, int|null $flags = null, array $fopenArgs = [], array $flockArgs = [])
 *
 * @method int|false file_echo(string $file, array $fopenArgs = [])
 * @method int|false file_echo_flock(string $file, int $modeLock, array $fopenArgs = [], array $flockArgs = [])
 * @method int|false file_echo_flock_pooling(int|null $tickUsleep, int|null $timeoutMs, string $file, int $modeLock, array $fopenArgs = [], array $flockArgs = [])
 *
 * @method string|null|false file_get_contents(string $file, int|null $offset = null, int|null $length = null, $context = null)
 * @method string|false file_put_contents(string $file, string|string[]|resource $data, int|null $flags = null, array $filePutContentsArgs = [], array|null $mkdirpArgs = [], array|null $chmodIfNewArgs = null)
 *
 * @method mixed call_safe(\Closure $fn, array $args = [])
 *
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
 *
 * // @ method fgetss()
 */
class FileSafeProxy
{
    /**
     * @var FileSafe
     */
    protected $inner;


    public function __construct(FileSafe $inner)
    {
        $this->inner = $inner;
    }


    /**
     * @return mixed
     */
    public function __call($name, $args)
    {
        /**
         * @var array<string, callable> $map
         */
        static $map;

        if ( null === $map ) {
            $map = [
                'umask'                       => 'umask',
                //
                'fopen'                       => [ '@inner', 'fopen' ],
                'fclose'                      => [ '@inner', 'fclose' ],
                'fclosef'                     => [ '@inner', 'fclosef' ],
                //
                'flock'                       => [ '@inner', 'flock' ],
                'flock_pooling'               => [ '@inner', 'flock_pooling' ],
                'frelease'                    => [ '@inner', 'frelease' ],
                'freleasef'                   => [ '@inner', 'freleasef' ],
                //
                'fopen_flock'                 => [ '@inner', 'fopen_flock' ],
                'fopen_flock_pooling'         => [ '@inner', 'fopen_flock_pooling' ],
                'frelease_fclose'             => [ '@inner', 'frelease_fclose' ],
                'freleasef_fclosef'           => [ '@inner', 'freleasef_fclosef' ],
                //
                'fopen_flock_tmpfile'         => [ '@inner', 'fopen_flock_tmpfile' ],
                'fopen_flock_tmpfile_pooling' => [ '@inner', 'fopen_flock_tmpfile_pooling' ],
                'frelease_fclose_unlink'      => [ '@inner', 'frelease_fclose_unlink' ],
                'freleasef_fclosef_unlinkf'   => [ '@inner', 'freleasef_fclosef_unlinkf' ],
                //
                'feof'                        => 'feof',
                'ftell'                       => 'ftell',
                'fseek'                       => [ '@inner', 'fseek' ],
                'rewind'                      => [ '@inner', 'rewind' ],
                //
                'fread'                       => [ '@inner', 'fread' ],
                'fwrite'                      => [ '@inner', 'fwrite' ],
                'fwrite_fflush'               => [ '@inner', 'fwrite_fflush' ],
                //
                'ftruncate'                   => 'ftruncate',
                //
                'fgetc'                       => 'fgetc',
                'fgets'                       => 'fgets',
                //
                'fputs'                       => 'fputs',
                'fputs_fflush'                => [ '@inner', 'fputs_fflush' ],
                //
                'fpassthru'                   => 'fpassthru',
                'fpassthru_fflush'            => [ '@inner', 'fpassthru_fflush' ],
                //
                'fflush'                      => 'fflush',
                'fsync'                       => 'fsync',
                'fdatasync'                   => 'fdatasync',
                //
                'filetype'                    => 'filetype',
                //
                'file_exists'                 => [ '@inner', 'file_exists' ],
                'is_exists'                   => [ '@inner', 'is_exists' ],
                'is_file'                     => [ '@inner', 'is_file' ],
                'is_realfile'                 => [ '@inner', 'is_realfile' ],
                'is_dir'                      => [ '@inner', 'is_dir' ],
                'is_realdir'                  => [ '@inner', 'is_realdir' ],
                //
                'is_link'                     => [ '@inner', 'is_link' ],
                'is_link_dir'                 => [ '@inner', 'is_link_dir' ],
                'is_link_file'                => [ '@inner', 'is_link_file' ],
                'is_link_target'              => [ '@inner', 'is_link_target' ],
                //
                'is_readable'                 => [ '@inner', 'is_readable' ],
                'is_writable'                 => [ '@inner', 'is_writable' ],
                'is_writeable'                => [ '@inner', 'is_writeable' ],
                'is_executable'               => [ '@inner', 'is_executable' ],
                //
                'clearstatcache'              => [ '@inner', 'clearstatcache' ],
                'fstat'                       => 'fstat',
                'lstat'                       => 'lstat',
                'stat'                        => 'stat',
                //
                'readlink'                    => 'readlink',
                'realpath'                    => [ '@inner', 'realpath' ],
                'realpath_link'               => [ '@inner', 'realpath_link' ],
                'realpath_target'             => [ '@inner', 'realpath_target' ],
                //
                'fileatime'                   => 'fileatime',
                'filectime'                   => 'filectime',
                'filegroup'                   => 'filegroup',
                'fileinode'                   => 'fileinode',
                'filemtime'                   => 'filemtime',
                'fileowner'                   => 'fileowner',
                'fileperms'                   => 'fileperms',
                'filesize'                    => 'filesize',
                //
                'chmod'                       => [ '@inner', 'chmod' ],
                'chown'                       => [ '@inner', 'chown' ],
                'lchown'                      => [ '@inner', 'lchown' ],
                'chgrp'                       => [ '@inner', 'chgrp' ],
                'lchgrp'                      => [ '@inner', 'lchgrp' ],
                //
                'touch'                       => [ '@inner', 'touch' ],
                'copy'                        => [ '@inner', 'copy' ],
                'rename'                      => [ '@inner', 'rename' ],
                //
                'unlink'                      => [ '@inner', 'unlink' ],
                'unlinkf'                     => [ '@inner', 'unlinkf' ],
                //
                'rm'                          => [ '@inner', 'rm' ],
                'rmf'                         => [ '@inner', 'rmf' ],
                //
                'mkdir'                       => [ '@inner', 'mkdir' ],
                'mkdirp'                      => [ '@inner', 'mkdirp' ],
                //
                'rmdir'                       => [ '@inner', 'rmdir' ],
                'rmdirf'                      => [ '@inner', 'rmdirf' ],
                //
                'link'                        => [ '@inner', 'link' ],
                'symlink'                     => [ '@inner', 'symlink' ],
                //
                'file_read'                   => [ '@inner', 'file_read' ],
                'file_read_flock'             => [ '@inner', 'file_read_flock' ],
                'file_read_flock_pooling'     => [ '@inner', 'file_read_flock_pooling' ],
                //
                'file_write'                  => [ '@inner', 'file_write' ],
                'file_write_flock'            => [ '@inner', 'file_write_flock' ],
                'file_write_flock_pooling'    => [ '@inner', 'file_write_flock_pooling' ],
                //
                'file'                        => false,
                'file_lines'                  => [ '@inner', 'file_lines' ],
                'file_lines_lock'             => [ '@inner', 'file_lines_flock' ],
                'file_lines_flock_pooling'    => [ '@inner', 'file_lines_flock_pooling' ],
                //
                'readfile'                    => false,
                'file_echo'                   => [ '@inner', 'file_echo' ],
                'file_echo_flock'             => [ '@inner', 'file_echo_flock' ],
                'file_echo_flock_pooling'     => [ '@inner', 'file_echo_flock_pooling' ],
                //
                'file_get_contents'           => [ '@inner', 'file_get_contents' ],
                'file_put_contents'           => [ '@inner', 'file_put_contents' ],
                //
                'call_safe'                   => [ '@inner', 'call_safe' ],
                //
                //
                'fgetcsv'                     => false,
                'fputcsv'                     => false,
                //
                'basename'                    => false,
                'dirname'                     => false,
                //
                'linkinfo'                    => false,
                'pathinfo'                    => false,
                //
                'pclose'                      => false,
                'popen'                       => false,
                //
                'glob'                        => false,
                //
                'parse_ini_file'              => false,
                'parse_ini_string'            => false,
                //
                'realpath_cache_get'          => false,
                'realpath_cache_size'         => false,
                //
                'set_file_buffer'             => false,
                //
                'tempnam'                     => false,
                'tmpfile'                     => false,
                //
                'is_uploaded_file'            => false,
                'move_uploaded_file'          => false,
                //
                'disk_free_space'             => false,
                'disk_total_space'            => false,
                'diskfreespace'               => false,
                //
                'fgetss'                      => false,
            ];
        }

        if ( empty($map[$name]) ) {
            throw new RuntimeException(
                [ 'Method is not exists: ' . $name ]
            );
        }

        $theFunc = Lib::func();

        $fn = $map[$name];

        if ( is_array($fn) ) {
            if ( '@inner' === $fn[0] ) {
                $fn[0] = $this->inner;
            }
        }

        $result = $theFunc->safe_call($fn, $args);

        return $result;
    }
}
