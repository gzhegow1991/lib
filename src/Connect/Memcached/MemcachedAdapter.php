<?php

namespace Gzhegow\Lib\Connect\Memcached;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\RuntimeException;


class MemcachedAdapter
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    /**
     * @var \Memcached
     */
    protected $configMemcached;
    /**
     * @var array
     */
    protected $configMemcachedOptionsNew = [];
    /**
     * @var array
     */
    protected $configMemcachedOptionsBoot = [];

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
    protected $configPort = 11211;
    /**
     * @var string
     */
    protected $configSock;
    // protected $configSock = '/var/run/memcached/memcached.sock';

    /**
     * @var int
     */
    protected $configWeight = 0;

    /**
     * @var string|null
     */
    protected $configNamespace;

    /**
     * @var array
     */
    protected $configUserOptions = [];

    /**
     * @var array
     */
    protected $configShardList = [];


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
            ?? static::fromMemcached($from)->orNull($ret)
            ?? static::fromArray($from)->orNull($ret);

        if ($ret->isFail()) {
            return Ret::throw($fallback, $ret);
        }

        return Ret::ok($fallback, $instance);
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromStatic($from, ?array $fallback = null)
    {
        if ($from instanceof static) {
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
    public static function fromMemcached($from, ?array $fallback = null)
    {
        if ($from instanceof \Memcached) {
            $instance = new static();
            $instance->memcached = $from;

            return Ret::ok($fallback, $instance);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . \Memcached::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|Ret<static>
     */
    public static function fromArray($from, ?array $fallback = null)
    {
        if (! is_array($from)) {
            return Ret::throw(
                $fallback,
                [ 'The `from` should be array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $theType = Lib::type();

        $from += [
            0                        => null, // dsn
            1                        => null, // namespace
            2                        => null, // memcached_options_new
            //
            'memcached'              => null,
            'memcached_options_new'  => null,
            'memcached_options_boot' => null,
            //
            'dsn'                    => null,
            'host'                   => null,
            'port'                   => null,
            'sock'                   => null,
            //
            'weight'                 => null,
            //
            'namespace'              => null,
            //
            'user_options'           => null,
            //
            'shard'                  => null,
        ];

        $dsn = $from[ 0 ];
        $namespace = $from[ 1 ];
        $memcachedOptionsNew = $from[ 2 ];

        if (null !== $dsn) {
            $from[ 'dsn' ] = $dsn;
        }
        if (null !== $namespace) {
            $from[ 'namespace' ] = $namespace;
        }
        if (null !== $memcachedOptionsNew) {
            $from[ 'memcached_options_new' ] = $memcachedOptionsNew;
        }

        $memcached = $from[ 'memcached' ];
        $memcachedOptionsNew = $from[ 'memcached_options_new' ] ?? [];
        $memcachedOptionsBoot = $from[ 'memcached_options_boot' ] ?? [];

        $dsn = $from[ 'dsn' ];
        $host = $from[ 'host' ];
        $port = $from[ 'port' ];
        $sock = $from[ 'sock' ];

        $weight = $from[ 'weight' ];

        $namespace = $from[ 'namespace' ];

        $userOptions = $from[ 'user_options' ] ?? [];

        $shardList = $from[ 'shard' ];

        $isMemcached = (null !== $memcached);
        $isDsn = (null !== $dsn);
        $isHost = (null !== $host);
        $isSock = (null !== $sock);

        if ($isMemcached) {
            if (! ($memcached instanceof \Memcached)) {
                return Ret::throw(
                    $fallback,
                    [ 'The `redis` should be instance of: ' . \Redis::class, $memcached ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $host = null;
            $port = null;
            $sock = null;

        } elseif ($isDsn) {
            $status = $theType->url(
                $dsn, null, null,
                0, 0,
                [ &$parseUrl ]
            )->isOk([ &$dsn, &$ret ]);

            if (false === $status) {
                return Ret::throw($fallback, $ret);
            }

            $host = $host ?? $parseUrl[ 'host' ] ?? null;
            $port = $port ?? $parseUrl[ 'port' ] ?? null;

            $query = $parseUrl[ 'query' ] ?? '';

            if ('' !== $query) {
                parse_str($query, $query);

                if ([] !== $query) {
                    $memcachedOptionsNew += $query;
                }
            }

        } elseif ($isHost) {
            $memcached = null;
            $sock = null;

        } elseif ($isSock) {
            $memcached = null;
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

        if ($isMemcached) {
            //

        } elseif ($isDsn || $isHost) {
            if (! $theType->string_not_empty($host)->isOk([ &$host, &$ret ])) {
                return Ret::throw($fallback, $ret);
            }

            $port = $port ?: 11211;

            if (! $theType->int_positive($port)->isOk([ &$port, &$ret ])) {
                return Ret::throw($fallback, $ret);
            }

        } elseif ($isSock) {
            if (! $theType->string_not_empty($sock)->isOk([ &$sock, &$ret ])) {
                return Ret::throw($fallback, $ret);
            }
        }

        $weight = $weight ?: 0;

        if (! $theType->int_non_negative($weight)->isOk([ &$weight, &$ret ])) {
            return Ret::throw($fallback, $ret);
        }

        if (null !== $namespace) {
            if (! $theType->string_not_empty($namespace)->isOk([ &$namespace, &$ret ])) {
                return Ret::throw($fallback, $ret);
            }
        }

        if (is_array($memcachedOptionsNew)) {
            $persistentId = $memcachedOptionsNew[ 'persistent_id' ] ?? null;
            $connectionStr = $memcachedOptionsNew[ 'connection_str' ] ?? null;

            if (null !== $persistentId) {
                if (! $theType->string_not_empty($persistentId)->isOk([ &$persistentId, &$ret ])) {
                    return Ret::throw($fallback, $ret);
                }

                $memcachedOptionsNew[ 'persistent_id' ] = $persistentId;
            }

            if (null !== $connectionStr) {
                if (! $theType->string_not_empty($connectionStr)->isOk([ &$connectionStr, &$ret ])) {
                    return Ret::throw($fallback, $ret);
                }

                $memcachedOptionsNew[ 'connection_str' ] = $connectionStr;
            }
        }

        $instance = new static();
        //
        $instance->configMemcached = $memcached;
        $instance->configMemcachedOptionsNew = $memcachedOptionsNew;
        $instance->configMemcachedOptionsBoot = $memcachedOptionsBoot;
        //
        $instance->configDsn = $dsn;
        $instance->configHost = $host;
        $instance->configPort = $port;
        $instance->configSock = $sock;
        //
        $instance->configWeight = $weight;
        //
        $instance->configNamespace = $namespace;
        //
        $instance->configUserOptions = $userOptions;

        if (is_array($shardList)) {
            $configDefault = $instance->getConfigDefault();

            // > `dsn` key is not supported for shard servers for now
            unset($configDefault[ 'dsn' ]);

            foreach ( $shardList as $i => $r ) {
                if (! is_array($r)) {
                    return Ret::throw(
                        $fallback,
                        [ 'Each of `from[shard]` should be array', $from, $r, $i ],
                        [ __FILE__, __LINE__ ]
                    );
                }

                if ($diff = array_diff_key($r, $configDefault)) {
                    return Ret::throw(
                        $fallback,
                        [
                            ''
                            . 'The `from[shard]` item contains unexpected keys: '
                            . implode('|', array_keys($diff)),
                            //
                            $from,
                            $r,
                            $i,
                        ],
                        [ __FILE__, __LINE__ ]
                    );
                }
            }

            $instance->configShardList = $shardList;
        }

        return Ret::ok($fallback, $instance);
    }


    public function getConfigDefault() : array
    {
        return [
            'memcached'              => $this->configMemcached,
            'memcached_options_new'  => $this->configMemcachedOptionsNew,
            'memcached_options_boot' => $this->configMemcachedOptionsBoot,
            //
            'dsn'                    => $this->configDsn,
            'host'                   => $this->configHost,
            'port'                   => $this->configPort,
            'sock'                   => $this->configSock,
            //
            'weight'                 => $this->configWeight,
            //
            'namespace'              => $this->configNamespace,
            //
            'user_options'           => $this->configUserOptions,
        ];
    }


    public function newMemcachedFromConfig(array $configValid) : \Memcached
    {
        $memcachedOptionsNew = $this->configMemcachedOptionsNew;

        $persistentId = $memcachedOptionsNew[ 'persistent_id' ] ?? null;
        $connectionStr = $memcachedOptionsNew[ 'connection_str' ] ?? null;

        $fnOnNewObjectCb = function (\Memcached $m) use ($configValid) {
            $this->memcachedAddServers($m, $configValid);
            $this->memcachedEnsureOptions($m, $configValid);
        };

        $memcached = new \Memcached(
            $persistentId,
            $fnOnNewObjectCb,
            $connectionStr
        );

        return $memcached;
    }


    public function isMemcached(?\Memcached &$memcached = null) : bool
    {
        $memcached = null;

        if (null !== $this->memcached) {
            $memcached = $this->memcached;

            return true;
        }

        return false;
    }

    public function getMemcached() : \Memcached
    {
        if (null === $this->memcached) {
            $configDefault = $this->getConfigDefault();

            $memcached = $this->newMemcachedFromConfig($configDefault);

            $this->memcached = $memcached;
        }

        return $this->memcached;
    }


    public function getShardConfigs() : array
    {
        $configDefault = $this->getConfigDefault();

        $configs = [];
        foreach ( $this->configShardList as $i => $config ) {
            $configs[ $i ] = []
                + $config
                + $configDefault;
        }

        return $configs;
    }


    protected function memcachedEnsureOptions(\Memcached $memcached, array $configValid) : void
    {
        $this->memcachedEnsureOptionsDefault($memcached, $configValid);
        $this->memcachedEnsureOptionsBoot($memcached, $configValid);
        $this->memcachedEnsureOptionsUser($memcached, $configValid);
    }

    protected function memcachedEnsureOptionsDefault(\Memcached $memcached, array $configValid) : void
    {
        $namespace = $this->configNamespace;

        if (null !== $namespace) {
            $memcached->setOption(\Memcached::OPT_PREFIX_KEY, "{$namespace}:");
        }

        $memcached->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_PHP);
        $memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 1000); // 1 second
    }

    protected function memcachedEnsureOptionsBoot(\Memcached $memcached, array $configValid) : void
    {
        $memcachedOptionsBoot = $configValid[ 'memcached_options_boot' ] ?? [];
        if ([] === $memcachedOptionsBoot) {
            return;
        }

        foreach ( $memcachedOptionsBoot as $memcachedOpt => $value ) {
            $status = $memcached->setOption($memcachedOpt, $value);

            if (false === $status) {
                throw new RuntimeException(
                    [ 'Unable to set `memcached_options_boot` on \PDO object', $memcachedOptionsBoot ]
                );
            }
        }
    }

    /**
     * @noinspection PhpUnnecessaryStopStatementInspection
     */
    protected function memcachedEnsureOptionsUser(\Memcached $memcached, array $configValid) : void
    {
        $userOptions = $configValid[ 'user_options' ] ?? [];
        if ([] === $userOptions) {
            return;
        }

        // > your own code
    }


    protected function memcachedAddServers(\Memcached $memcached, array $configValid) : void
    {
        $serverList = [];

        $host = $this->configHost;
        $port = $this->configPort;
        $sock = $this->configSock;
        $weight = $this->configWeight;
        $shardList = $this->configShardList;

        if (null !== $sock) {
            $serverList[ "{$sock}:0" ] = [ $sock, 0, $weight ];

        } else {
            // } elseif (null !== $host) {
            $serverList[ "{$host}:{$port}" ] = [ $host, $port, $weight ];
        }

        if (is_array($shardList)) {
            foreach ( $shardList as $s ) {
                $s += $configValid;

                $sHost = $s[ 'host' ];
                $sPort = $s[ 'port' ];
                $sSock = $s[ 'sock' ];
                $sWeight = $s[ 'weight' ];

                if (null !== $sSock) {
                    $key = "{$sSock}:0";

                    if (isset($serverList[ $key ])) {
                        throw new RuntimeException(
                            [ 'This server key is already registered: ' . $key ]
                        );
                    }

                    $serverList[ $key ] = [ $sSock, 0, $sWeight ];

                } elseif (null !== $sHost) {
                    $key = "{$sHost}:{$sPort}";

                    if (isset($serverList[ $key ])) {
                        throw new RuntimeException(
                            [ 'This server key is already registered: ' . $key ]
                        );
                    }

                    $serverList[ $key ] = [ $sHost, $sPort, $sWeight ];
                }
            }
        }

        foreach ( $serverList as $args ) {
            $memcached->addServer(...$args);
        }
    }
}
