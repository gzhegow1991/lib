<?php

namespace Gzhegow\Lib\Connect\Redis;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
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
     * @var string|null
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
     * @return static|Ret<static>
     */
    public static function from($from, ?array $fallback = null)
    {
        $ret = Ret::new();

        $instance = null
            ?? static::fromStatic($from)->orNull($ret)
            ?? static::fromRedis($from)->orNull($ret)
            ?? static::fromArrayDsn($from)->orNull($ret)
            ?? static::fromArrayConfig($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
            return Ret::val($fallback, $from);
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
        if ($from instanceof \Redis) {
            $instance = new static();
            $instance->redis = $from;

            return Ret::val($fallback, $instance);
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
    public static function fromArrayDsn($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be a non-empty array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'dsn' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[dsn]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $redisDsn = $from[ 'dsn' ];
        $redisDsnParsed = parse_url($redisDsn);

        if (! isset(
            $redisDsnParsed[ 'scheme' ],
            $redisDsnParsed[ 'host' ],
            $redisDsnParsed[ 'port' ]
        )) {
            return Ret::throw(
                $fallback,
                [ 'The `from[dsn]` is invalid', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ('redis' !== $redisDsnParsed[ 'scheme' ]) {
            return Ret::throw(
                $fallback,
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

        return Ret::val($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArrayConfig($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be a non-empty array', $from ],
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

        return Ret::val($fallback, $instance);
    }


    public function newRedis() : \Redis
    {
        $theType = Lib::$type;

        $redisHost = $this->redisHost;
        $redisPort = $this->redisPort;
        $redisPassword = $this->redisPassword;
        $redisNamespace = $this->redisNamespace;

        $redisHostValid = $theType->string_not_empty($redisHost)->orThrow();
        $redisPortValid = $theType->numeric_int_positive($redisPort)->orThrow();

        $redisPasswordValid = null;
        if (null !== $redisPassword) {
            $redisPasswordValid = $theType->string($redisPassword)->orThrow();
        }

        $redisNamespaceValid = null;
        if (null !== $redisNamespace) {
            $redisNamespaceValid = $theType->string_not_empty($redisNamespace)->orThrow();
        }

        $redis = new \Redis();

        $this->redisSafeConnect($redis, $redisHostValid, $redisPortValid);
        $this->redisSafeAuth($redis, $redisPasswordValid);

        $this->redisEnsureOptions($redis, $redisNamespaceValid);

        return $redis;
    }


    public function getRedis() : \Redis
    {
        $theType = Lib::$type;

        if (! $this->isRedisInitialized) {
            if (null === $this->redis) {
                $redis = $this->newRedis();

            } else {
                $redis = $this->redis;
                $redisNamespace = $this->redisNamespace;

                $redisNamespaceValid = null;
                if (null !== $redisNamespace) {
                    $redisNamespaceValid = $theType->string_not_empty($redisNamespace)->orThrow();
                }

                $this->redisEnsureOptions($redis, $redisNamespaceValid);
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


    protected function redisEnsureOptions(
        \Redis $redis,
        ?string $redisNamespace
    ) : void
    {
        // > namespace
        if (null !== $redisNamespace) {
            $redis->setOption(\Redis::OPT_PREFIX, "{$redisNamespace}:");
        }

        // > serializer
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        // > timeout
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, 3.0);
    }


    protected function redisSafeConnect(
        \Redis $redis,
        string $redisHost, int $redisPort
    ) : bool
    {
        $theFunc = Lib::$func;

        try {
            $status = $theFunc->safe_call(
                [ $redis, 'connect' ],
                [ $redisHost, $redisPort ]
            );
        }
        catch ( \Throwable $e ) {
            throw new RemoteException(
                [ 'Unable to connect to Redis: ' . $e->getMessage() ], $e
            );
        }

        return $status;
    }

    protected function redisSafeAuth(
        \Redis $redis,
        ?string $redisPassword
    ) : ?bool
    {
        if (null === $redisPassword) {
            return null;
        }

        $theFunc = Lib::$func;

        try {
            $status = $theFunc->safe_call(
                [ $redis, 'auth' ],
                [ $redisPassword ]
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
