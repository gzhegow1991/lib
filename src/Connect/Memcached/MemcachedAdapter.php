<?php

namespace Gzhegow\Lib\Connect\Memcached;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\Runtime\RemoteException;


class MemcachedAdapter
{
    /**
     * @var bool
     */
    protected $isMemcachedInitialized = false;

    /**
     * @var string|null
     */
    protected $memcachedNamespace;
    /**
     * @var string
     */
    protected $memcachedHost = '127.0.0.1';
    /**
     * @var int
     */
    protected $memcachedPort = 11211;
    /**
     * @var array
     */
    protected $memcachedOptions = [];

    /**
     * @var \Memcached
     */
    protected $memcached;


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
    public static function fromMemcached($from, ?array $fallback = null)
    {
        if ($from instanceof \Memcached) {
            $instance = new static();
            $instance->memcached = $from;

            return Ret::val($fallback, $instance);
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
    public static function fromArrayDsn($from, ?array $fallback = null)
    {
        if (! is_array($from) || ! isset($from[ 'dsn' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[dsn]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $memcachedDsnParsed = parse_url($from[ 'dsn' ]);

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

        if ('memcached' !== $redisDsnParsed[ 'scheme' ]) {
            return Ret::throw(
                $fallback,
                [ 'The `from[dsn]` is invalid', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        $instance->memcachedHost = $memcachedDsnParsed[ 'host' ];
        $instance->memcachedPort = $memcachedDsnParsed[ 'port' ];

        $instance->memcachedNamespace = $from[ 'namespace' ] ?? $from[ 0 ] ?? null;
        $instance->memcachedOptions = $from[ 'options' ] ?? [];

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
                [ 'Invalid config array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        $instance->memcachedHost = $from[ 'host' ] ?? '127.0.0.1';
        $instance->memcachedPort = $from[ 'port' ] ?? 11211;
        $instance->memcachedNamespace = $from[ 'namespace' ] ?? null;
        $instance->memcachedOptions = $from[ 'options' ] ?? [];

        return Ret::val($fallback, $instance);
    }


    public function newMemcached() : \Memcached
    {
        $theType = Lib::type();

        $memcachedHost = $this->memcachedHost;
        $memcachedPort = $this->memcachedPort;

        $memcachedHostValid = $theType->string_not_empty($memcachedHost)->orThrow();
        $memcachedPortValid = $theType->numeric_int_positive($memcachedPort)->orThrow();

        $memcached = new \Memcached();

        $memcached->addServer($memcachedHostValid, $memcachedPortValid);

        $this->memcachedEnsureOptions($memcached);

        return $memcached;
    }


    public function getMemcached() : \Memcached
    {
        if (! $this->isMemcachedInitialized) {
            if (null === $this->memcached) {
                $memcached = $this->newMemcached();

            } else {
                $memcached = $this->memcached;

                $this->memcachedEnsureOptions($memcached);
            }

            if (null === $memcached) {
                throw new RemoteException(
                    [ 'Unable to ' . __METHOD__, $this ]
                );
            }

            $this->memcached = $memcached;

            $this->memcachedHost = null;
            $this->memcachedPort = null;
            $this->memcachedNamespace = null;
            $this->memcachedOptions = null;

            $this->isMemcachedInitialized = true;
        }

        return $this->memcached;
    }

    protected function memcachedEnsureOptions(\Memcached $memcached) : void
    {
        if (null !== $this->memcachedNamespace) {
            $memcached->setOption(\Memcached::OPT_PREFIX_KEY, "{$this->memcachedNamespace}:");
        }

        $memcached->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_PHP);
        $memcached->setOption(\Memcached::OPT_CONNECT_TIMEOUT, 1000); // 1 second

        foreach ( $this->memcachedOptions as $option => $value ) {
            if (is_string($option) && defined($option)) {
                $option = constant($option);
            }

            $memcached->setOption($option, $value);
        }
    }
}
