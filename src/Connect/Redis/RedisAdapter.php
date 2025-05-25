<?php

namespace Gzhegow\Lib\Connect\Redis;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Ret;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\RemoteException;


class RedisAdapter
{
    /**
     * @var bool
     */
    protected $isRedisInitialized = false;

    /**
     * @var string
     */
    protected $redisDsn;

    /**
     * @var string|null
     */
    protected $redisNamespace;

    /**
     * @var string
     */
    protected $redisHost = '127.0.0.1';
    /**
     * @var string
     */
    protected $redisPort = 6379;

    /**
     * @var string
     */
    protected $redisPassword;

    /**
     * @var array
     */
    protected $redisOptions = [];

    /**
     * @var \Redis
     */
    protected $redis;


    private function __construct()
    {
    }


    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function from($from, $ret = null)
    {
        $retCur = Result::asValue();

        $instance = null
            ?? static::fromStatic($from, $retCur)
            ?? static::fromRedis($from, $retCur)
            ?? static::fromArrayDsn($from, $retCur)
            ?? static::fromArrayConfig($from, $retCur);

        if ($retCur->isErr()) {
            return Result::err($ret, $retCur);
        }

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromStatic($from, $ret = null)
    {
        if ($from instanceof static) {
            return Result::ok($ret, $from);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromRedis($from, $ret = null)
    {
        if ($from instanceof \Redis) {
            $instance = new static();
            $instance->redis = $from;

            return Result::ok($ret, $instance);
        }

        return Result::err(
            $ret,
            [ 'The `from` should be instance of: ' . \PDO::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromArrayDsn($from, $ret = null)
    {
        if (! (is_array($from) && ([] !== $from))) {
            return Result::err(
                $ret,
                [ 'The `from` should be non-empty array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'dsn' ])) {
            return Result::err(
                $ret,
                [ 'The `from[dsn]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $redisDsn = $from[ 'dsn' ];
        $redisDsnParsed = parse_url($redisDsn);

        if (! isset($redisDsnParsed[ 'scheme' ], $redisDsnParsed[ 'host' ], $redisDsnParsed[ 'port' ])) {
            return Result::err(
                $ret,
                [ 'The `from[dsn]` is invalid', $from ],
                [ __FILE__, __LINE__ ]
            );
        }
        if ('redis' !== $redisDsnParsed[ 'scheme' ]) {
            return Result::err(
                $ret,
                [ 'The `from[dsn]` is invalid', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $redisHost = $redisDsnParsed[ 'host' ];
        $redisPort = $redisDsnParsed[ 'port' ];

        $redisNamespace = $from[ 'namespace' ] ?? $from[ 0 ] ?? null;
        $redisPassword = $from[ 'password' ] ?? $from[ 1 ] ?? null;
        $redisOptions = $from[ 'options' ] ?? [];

        $instance = new static();

        $instance->redisNamespace = $redisNamespace;
        $instance->redisHost = $redisHost;
        $instance->redisPort = $redisPort;
        $instance->redisPassword = $redisPassword;
        $instance->redisOptions = $redisOptions;

        $instance->redisDsn = $from[ 'dsn' ];

        return Result::ok($ret, $instance);
    }

    /**
     * @param Ret $ret
     *
     * @return static|bool|null
     */
    public static function fromArrayConfig($from, $ret = null)
    {
        if (! (is_array($from) && ([] !== $from))) {
            return Result::err(
                $ret,
                [ 'The `from` should be non-empty array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $redisHost = $from[ 'host' ] ?? '127.0.0.1';
        $redisPort = $from[ 'port' ] ?? 6379;
        $redisPassword = $from[ 'password' ] ?? null;
        $redisNamespace = $from[ 'namespace' ] ?? null;
        $redisOptions = $from[ 'options' ] ?? [];

        $instance = new static();

        $instance->redisNamespace = $redisNamespace;
        $instance->redisHost = $redisHost;
        $instance->redisPort = $redisPort;
        $instance->redisPassword = $redisPassword;
        $instance->redisOptions = $redisOptions;

        return Result::ok($ret, $instance);
    }


    public function newRedis() : \Redis
    {
        $redisHost = $this->redisHost;
        $redisPort = $this->redisPort;
        $redisPassword = $this->redisPassword;
        $redisNamespace = $this->redisNamespace;

        if (! (is_string($redisHost) && ('' !== $redisHost))) {
            throw new RuntimeException(
                [ 'The `this[pdoHost]` should be non-empty string', $this ]
            );
        }
        if (! (Lib::type()->string_not_empty($redisPortString, $redisPort))) {
            throw new RuntimeException(
                [ 'The `this[pdoPort]` should be non-empty string', $this ]
            );
        }
        if (null !== $redisPassword) {
            if (! (is_string($redisPassword))) {
                throw new RuntimeException(
                    [ 'The `this[redisPassword]` should be string', $this ]
                );
            }
        }
        if (null !== $redisNamespace) {
            if (! (is_string($redisNamespace) && ('' !== $redisNamespace))) {
                throw new RuntimeException(
                    [ 'The `this[redisNamespace]` should be non-empty string', $this ]
                );
            }
        }

        $redis = new \Redis();

        $this->redisSafeConnect($redis);
        $this->redisSafeAuth($redis);

        $this->redisEnsureOptions($redis);

        return $redis;
    }


    public function getRedis() : \Redis
    {
        if (! $this->isRedisInitialized) {
            if (null === $this->redis) {
                $redis = $this->newRedis();

            } else {
                $redis = $this->redis;

                $this->redisEnsureOptions($redis);
            }

            if (null === $redis) {
                throw new RemoteException(
                    [ 'Unable to ' . __METHOD__, $this ]
                );
            }

            $this->redis = $redis;

            $this->redisDsn = null;
            $this->redisNamespace = null;
            $this->redisHost = null;
            $this->redisPort = null;
            $this->redisPassword = null;
            $this->redisOptions = null;

            $this->isRedisInitialized = true;
        }

        return $this->redis;
    }


    protected function redisEnsureOptions(\Redis $redis) : void
    {
        // > namespace
        if (null !== $this->redisNamespace) {
            $redis->setOption(\Redis::OPT_PREFIX, "{$this->redisNamespace}:");
        }

        // > serializer
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        // > timeout
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, 3.0);
    }


    protected function redisSafeConnect(\Redis $redis) : bool
    {
        try {
            $status = Lib::func()->safe_call(
                [ $redis, 'connect' ],
                [ $this->redisHost, $this->redisPort ]
            );
        }
        catch ( \Throwable $e ) {
            throw new RemoteException(
                [ 'Unable to connect to Redis: ' . $e->getMessage() ], $e
            );
        }

        return $status;
    }

    protected function redisSafeAuth(\Redis $redis) : ?bool
    {
        if (null === $this->redisPassword) {
            return null;
        }

        try {
            $status = Lib::func()->safe_call(
                [ $redis, 'auth' ],
                [ $this->redisPassword ]
            );
        }
        catch ( \Throwable $e ) {
            throw new RemoteException(
                [ 'Unable to authorize into Redis: ' . $e->getMessage() ], $e
            );
        }

        return $status;
    }
}
