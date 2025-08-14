<?php

namespace Gzhegow\Lib\Modules\Http\Session\SessionSafe;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Exception\RuntimeException;


/**
 * @method bool session_abort()
 * @method int|false session_cache_expire(int|null $value = null)
 * @method string|false session_cache_limiter(string|null $value = null)
 * @method bool session_commit()
 * @method string|false session_create_id(string $prefix = '')
 * @method bool session_decode(string $data)
 * @method bool session_destroy()
 * @method string|false session_encode()
 * @method int|false session_gc()
 * @method array session_get_cookie_params()
 * @method string|false session_id(string|null $id = null)
 * @method string|false session_module_name(string|null $module = null)
 * @method string|false session_name(string|null $name = null)
 * @method bool session_regenerate_id(bool $delete_old_session = false)
 * @method void session_register_shutdown()
 * @method bool session_reset()
 * @method string|false session_save_path(string|null $path = null)
 * @method bool session_set_cookie_params(int|array $lifetime_or_options, string|null $path = null, string|null $domain = null, bool|null $secure = null, bool|null $httponly = null)
 * @method bool session_set_save_handler(callable $open, callable $close, callable $read, callable $write, callable $destroy, callable $gc, callable|null $create_sid = null, callable|null $validate_sid = null, callable|null $update_timestamp = null)
 * @method bool session_start(array $options = [])
 * @method int session_status()
 * @method bool session_unset()
 * @method bool session_write_close()
 *
 * @method bool has(string $key, &$refValue = null) : bool
 * @method mixed get(string $key)
 * @method self set(string $key, $value)
 * @method self unset(string $key)
 * @method self clear()
 */
class SessionSafeProxy
{
    /**
     * @var SessionSafe
     */
    protected $inner;


    public function __construct(SessionSafe $inner)
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

        if (null === $map) {
            $map = [
                'session_abort'             => 'session_abort',
                'session_cache_expire'      => 'session_cache_expire',
                'session_cache_limiter'     => 'session_cache_limiter',
                'session_commit'            => 'session_commit',
                'session_create_id'         => 'session_create_id',
                'session_decode'            => 'session_decode',
                'session_destroy'           => 'session_destroy',
                'session_encode'            => 'session_encode',
                'session_gc'                => 'session_gc',
                'session_get_cookie_params' => 'session_get_cookie_params',
                'session_id'                => 'session_id',
                'session_module_name'       => 'session_module_name',
                'session_name'              => 'session_name',
                'session_regenerate_id'     => 'session_regenerate_id',
                'session_register_shutdown' => 'session_register_shutdown',
                'session_reset'             => 'session_reset',
                'session_save_path'         => 'session_save_path',
                'session_set_cookie_params' => 'session_set_cookie_params',
                'session_set_save_handler'  => 'session_set_save_handler',
                'session_start'             => 'session_start',
                'session_status'            => 'session_status',
                'session_unset'             => 'session_unset',
                'session_write_close'       => 'session_write_close',
                //
                'has'                       => [ '@inner', 'has' ],
                'get'                       => [ '@inner', 'get' ],
                'set'                       => [ '@inner', 'set' ],
                'unset'                     => [ '@inner', 'unset' ],
                'clear'                     => [ '@inner', 'clear' ],
            ];
        }

        if (empty($map[ $name ])) {
            throw new RuntimeException(
                [ 'Method is not exists: ' . $name ]
            );
        }

        $theFunc = Lib::func();

        $fn = $map[ $name ];

        if (is_array($fn)) {
            if ('@inner' === $fn[ 0 ]) {
                $fn[ 0 ] = $this->inner;
            }
        }

        $result = $theFunc->safe_call($fn, $args);

        return $result;
    }
}
