<?php

namespace Gzhegow\Lib\Connect\Redis;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\RemoteException;


class RedisAdapter
{
    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * @var \Redis
     */
    protected $configRedis;
    /**
     * @var array
     */
    protected $configRedisOptionsNew = [];
    /**
     * @var array
     */
    protected $configRedisOptionsBoot = [];

    /**
     * @var string
     */
    protected $configDsn;

    /**
     * @var string
     */
    protected $configHost = '127.0.0.1';
    /**
     * @var int
     */
    protected $configPort = 6379;
    /**
     * @var string
     */
    protected $configSock;
    // protected $configSock = '/var/run/redis/redis.sock';

    /**
     * @var array{ 0?: string, 1?: string }|null
     */
    protected $configCredentials;
    /**
     * @var string|null
     */
    protected $configPassword;

    /**
     * @var int
     */
    protected $configDatabase = 0;
    /**
     * @var string|null
     */
    protected $configNamespace;

    /**
     * @var array
     */
    protected $configUserOptions = [];

    /**
     * @var \Closure
     */
    protected $fnEnsureOptionsUser;


    private function __construct()
    {
    }


    /**
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromRedis($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret);

        if ( $ret->isFail() ) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ( $from instanceof static ) {
            return Ret::ok($fallback, $from);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromRedis($from, ?array $fallback = null)
    {
        if ( $from instanceof \Redis ) {
            $instance = new static();
            $instance->redis = $from;

            return Ret::ok($fallback, $instance);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . \Redis::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArray($from, ?array $fallback = null)
    {
        if ( ! is_array($from) ) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $from += [
            0                    => null, // dsn
            1                    => null, // credentials / password
            2                    => null, // namespace
            3                    => null, // redis_options_new
            //
            'redis'              => null,
            'redis_options_new'  => null,
            'redis_options_boot' => null,
            //
            'dsn'                => null,
            'host'               => null,
            'port'               => null,
            'sock'               => null,
            //
            'credentials'        => null,
            'password'           => null,
            //
            'database'           => null,
            'namespace'          => null,
            //
            'user_options'       => null,
        ];

        $dsn = $from[0];
        $credentials = $password = $from[1];
        $namespace = $from[2];
        $redisOptionsNew = $from[3];

        if ( null !== $dsn ) {
            $from['dsn'] = $dsn;
        }
        if ( is_array($credentials) ) {
            $from['credentials'] = $credentials;
        }
        if ( is_string($password) ) {
            $from['password'] = $password;
        }
        if ( null !== $namespace ) {
            $from['namespace'] = $namespace;
        }

        if ( null !== $redisOptionsNew ) {
            $from['redis_options_new'] = $redisOptionsNew;
        }

        $redis = $from['redis'];
        $redisOptionsNew = $from['redis_options_new'] ?? [];
        $redisOptionsBoot = $from['redis_options_boot'] ?? [];

        $dsn = $from['dsn'];
        $host = $from['host'];
        $port = $from['port'];
        $sock = $from['sock'];

        $credentials = $from['credentials'];
        $password = $from['password'];

        $database = $from['database'];
        $namespace = $from['namespace'];

        $userOptions = $from['user_options'] ?? [];

        $isRedis = (null !== $redis);
        $isDsn = (null !== $dsn);
        $isHost = (null !== $host);
        $isSock = (null !== $sock);

        if ( $isRedis ) {
            if ( ! ($redis instanceof \Redis) ) {
                return Ret::throw(
                    $fallback,
                    [ 'The `redis` should be instance of: ' . \Redis::class, $redis ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $host = null;
            $port = null;
            $sock = null;

        } elseif ( $isDsn ) {
            if ( ! $theType->url(
                $dsn, null, null,
                0, 0,
                [ &$parseUrl ]
            )->isOk([ &$dsn, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }

            $host = $host ?? $parseUrl['host'] ?? null;
            $port = $port ?? $parseUrl['port'] ?? null;
            $password = $password ?? $parseUrl['pass'] ?? null;

            $path = $parseUrl['path'] ?? '';
            $query = $parseUrl['query'] ?? '';

            if ( '' !== $path ) {
                $pos = strrpos($path, '/');

                if ( $pos !== false ) {
                    $database = $database ?? substr($path, $pos + 1);
                    $path = substr($path, 0, $pos);

                    if ( basename($path, '.sock') !== basename($path) ) {
                        $sock = $path;
                    }
                }
            }

            if ( '' !== $query ) {
                parse_str($query, $query);

                if ( [] !== $query ) {
                    $redisOptionsNew += $query;
                }
            }

        } elseif ( $isHost ) {
            $redis = null;
            $sock = null;

        } elseif ( $isSock ) {
            $redis = null;
            $host = null;
            $port = null;

        } else {
            return Ret::throw(
                $fallback,
                [
                    ''
                    . 'The `from` should contain at least one of: '
                    . '(`redis`) or '
                    . '(`dsn`) or '
                    . '(`host` and `port`) or '
                    . '(`sock`)',
                    //
                    $from,
                ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $isRedis ) {
            //

        } elseif ( $isDsn || $isHost ) {
            if ( ! $theType->string_not_empty($host)->isOk([ &$host, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }

            $port = $port ?: 6379;

            if ( ! $theType->int_positive($port)->isOk([ &$port, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }

        } elseif ( $isSock ) {
            if ( ! $theType->string_not_empty($sock)->isOk([ &$sock, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( null !== $credentials ) {
            if ( ! $theType->list_sorted($credentials)->isOk([ &$credentials, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
            if ( ! $theType->string_not_empty($credentials[0] ?? null)->isOk([ 1 => &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
            if ( ! $theType->string($credentials[1] ?? null)->isOk([ 1 => &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( null !== $password ) {
            if ( ! $theType->string($password)->isOk([ &$password, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        $database = $database ?: '0';

        if ( ! $theType->int_non_negative($database)->isOk([ &$database, &$ret ]) ) {
            return Ret::throw($fallback, $ret);
        }

        if ( null !== $namespace ) {
            if ( ! $theType->string_not_empty($namespace)->isOk([ &$namespace, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        $instance = new static();
        //
        $instance->configRedis = $redis;
        $instance->configRedisOptionsNew = $redisOptionsNew;
        $instance->configRedisOptionsBoot = $redisOptionsBoot;
        //
        $instance->configDsn = $dsn;
        $instance->configHost = $host;
        $instance->configPort = $port;
        $instance->configSock = $sock;
        //
        $instance->configCredentials = $credentials;
        $instance->configPassword = $password;
        //
        $instance->configDatabase = $database;
        $instance->configNamespace = $namespace;
        //
        $instance->configUserOptions = $userOptions;

        return Ret::ok($fallback, $instance);
    }


    public function getConfig() : array
    {
        return [
            'redis'              => $this->configRedis,
            'redis_options_new'  => $this->configRedisOptionsNew,
            'redis_options_boot' => $this->configRedisOptionsBoot,
            //
            'dsn'                => $this->configDsn,
            'host'               => $this->configHost,
            'port'               => $this->configPort,
            'sock'               => $this->configSock,
            //
            'credentials'        => $this->configCredentials,
            'password'           => $this->configPassword,
            //
            'database'           => $this->configDatabase,
            'namespace'          => $this->configNamespace,
            //
            'user_options'       => $this->configUserOptions,
        ];
    }


    public function newRedisFromConfig(array $configValid) : \Redis
    {
        $configValid += [
            'redis'              => null,
            'redis_options_new'  => null,
            'redis_options_boot' => null,
            //
            'dsn'                => null,
            'host'               => null,
            'port'               => null,
            'sock'               => null,
            //
            'credentials'        => null,
            'password'           => null,
            //
            'database'           => null,
            'namespace'          => null,
            //
            'user_options'       => null,
        ];

        if ( null !== $configValid['redis'] ) {
            $redis = $configValid['redis'];

        } else {
            $redisOptionsNew = $configValid['redis_options_new'];

            try {
                $redis = new \Redis($redisOptionsNew);
            }
            catch ( \Throwable $e ) {
                throw new RemoteException(
                    [ 'Unable to ' . __METHOD__, $this ], $e
                );
            }
        }

        $this->redisSafeConnect($redis, $configValid);
        $this->redisSafeAuth($redis, $configValid);
        $this->redisSafeSelectDatabase($redis, $configValid);

        $this->redisEnsureOptions($redis, $configValid);

        return $redis;
    }


    public function isRedis(?\Redis &$redis = null) : bool
    {
        $redis = null;

        if ( null !== $this->redis ) {
            $redis = $this->redis;

            return true;
        }

        return false;
    }

    public function getRedis() : \Redis
    {
        if ( null === $this->redis ) {
            $config = $this->getConfig();

            $redis = $this->newRedisFromConfig($config);

            $this->redis = $redis;
        }

        return $this->redis;
    }


    /**
     * @return static
     */
    public function setFnEnsureOptionsUser(?\Closure $fnEnsureOptionsUser)
    {
        $this->fnEnsureOptionsUser = $fnEnsureOptionsUser;

        return $this;
    }


    protected function redisEnsureOptions(\Redis $redis, array $configValid) : void
    {
        $this->redisEnsureOptionsDefault($redis, $configValid);
        $this->redisEnsureOptionsBoot($redis, $configValid);
        $this->redisEnsureOptionsUser($redis, $configValid);
    }

    protected function redisEnsureOptionsDefault(\Redis $redis, array $configValid) : void
    {
        $namespace = $configValid['namespace'];

        if ( null !== $namespace ) {
            $redis->setOption(\Redis::OPT_PREFIX, "{$namespace}:");
        }

        // > serializer
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        // > timeout
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, 3.0);
    }

    protected function redisEnsureOptionsBoot(\Redis $redis, array $configValid) : void
    {
        $redisOptionsBoot = $configValid['redis_options_boot'] ?? [];
        if ( [] === $redisOptionsBoot ) {
            return;
        }

        foreach ( $redisOptionsBoot as $redisOpt => $value ) {
            $status = $redis->setOption($redisOpt, $value);

            if ( false === $status ) {
                throw new RuntimeException(
                    [ 'Unable to set `pdo_options` on \PDO object', $redisOptionsBoot ]
                );
            }
        }
    }

    protected function redisEnsureOptionsUser(\Redis $redis, array $configValid) : void
    {
        $fn = $this->fnEnsureOptionsUser;

        if ( null !== $fn ) {
            call_user_func($fn, $redis, $configValid, $this);
        }
    }


    protected function redisSafeConnect(\Redis $redis, array $configValid) : void
    {
        $theFunc = Lib::func();

        $host = $configValid['host'];
        $port = $configValid['port'];
        $sock = $configValid['sock'];

        $connectArgs = [];
        if ( null !== $sock ) {
            $connectArgs[] = $sock;

        } elseif ( (null !== $host) && (null !== $port) ) {
            $connectArgs[] = $host;
            $connectArgs[] = $port;

        } else {
            throw new RuntimeException(
                [ 'At least one of (`sock`) or (`host` and `port`) is required', $configValid ]
            );
        }

        $error = 'Status is false';
        $exception = null;
        try {
            $status = $theFunc->safe_call(
                [ $redis, 'connect' ],
                $connectArgs
            );
        }
        catch ( \Throwable $e ) {
            $status = false;

            $error = $e->getMessage();
            $exception = $e;
        }

        if ( false === $status ) {
            throw new RemoteException(
                [ 'Unable to ' . __FUNCTION__ . ': ' . $error ],
                $exception
            );
        }
    }

    protected function redisSafeAuth(\Redis $redis, array $configValid) : void
    {
        $theFunc = Lib::func();

        $redisCredentials = $configValid['credentials'];
        $redisPassword = $configValid['password'];

        if ( null !== $redisCredentials ) {
            $authArgs = [ $redisCredentials ];

        } elseif ( null !== $redisPassword ) {
            $authArgs = [ $redisPassword ];

        } else {
            return;
        }

        $error = 'Status is false';
        $exception = null;
        try {
            $status = $theFunc->safe_call(
                [ $redis, 'auth' ],
                $authArgs
            );
        }
        catch ( \Throwable $e ) {
            $status = false;

            $error = $e->getMessage();
            $exception = $e;
        }

        if ( false === $status ) {
            throw new RemoteException(
                [ 'Unable to ' . __METHOD__ . ': ' . $error ],
                $exception
            );
        }
    }

    protected function redisSafeSelectDatabase(\Redis $redis, array $configValid) : void
    {
        $theFunc = Lib::func();

        $database = $configValid['database'];

        $error = 'Status is false';
        $exception = null;
        try {
            $status = $theFunc->safe_call(
                [ $redis, 'select' ],
                [ $database ]
            );
        }
        catch ( \Throwable $e ) {
            $status = false;

            $error = $e->getMessage();
            $exception = $e;
        }

        if ( false === $status ) {
            throw new RemoteException(
                [ 'Unable to ' . __METHOD__ . ': ' . $error ],
                $exception
            );
        }
    }
}
