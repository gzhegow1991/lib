<?php

namespace Gzhegow\Lib\Connect\Pdo;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\RemoteException;


class PdoAdapter
{
    /**
     * @var bool
     */
    protected $isPdoInitialized = false;

    /**
     * @var string
     */
    protected $pdoDsn;

    /**
     * @var string|null
     */
    protected $pdoDatabase;

    /**
     * @var string
     */
    protected $pdoDriver;
    /**
     * @var string
     */
    protected $pdoHost = '127.0.0.1';
    /**
     * @var string
     */
    protected $pdoPort;
    /**
     * @var string
     */
    protected $pdoUsername;
    /**
     * @var string
     */
    protected $pdoPassword;

    /**
     * @var string|null
     */
    protected $pdoCharset = 'utf8';
    /**
     * @var string|null
     */
    protected $pdoCollate = 'utf8_unicode_ci';

    /**
     * @var array
     */
    protected $pdoOptions = [];

    /**
     * @var \PDO
     */
    protected $pdo;


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
            ?? static::fromPdo($from)->orNull($ret)
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
    public static function fromPdo($from, ?array $fallback = null)
    {
        if ($from instanceof \PDO) {
            $instance = new static();
            $instance->pdo = $from;

            return Ret::val($fallback, $instance);
        }

        return Ret::throw(
            $fallback,
            [ 'The `from` should be an instance of: ' . \PDO::class, $from ],
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
                [ 'The `from` should be array', $from ],
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

        $pdoUsername = $from[ 'username' ] ?? $from[ 0 ] ?? null;
        $pdoPassword = $from[ 'password' ] ?? $from[ 1 ] ?? null;
        $pdoDatabase = $from[ 'database' ] ?? null;
        $pdoOptions = $from[ 'options' ] ?? [];

        if (null === $pdoUsername) {
            return Ret::throw(
                $fallback,
                [ 'The `from[username]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === $pdoPassword) {
            return Ret::throw(
                $fallback,
                [ 'The `from[password]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        $instance->pdoUsername = $pdoUsername;
        $instance->pdoPassword = $pdoPassword;
        $instance->pdoDatabase = $pdoDatabase;
        $instance->pdoOptions = $pdoOptions;

        $instance->pdoDsn = $from[ 'dsn' ];

        if (isset($from[ 'charset' ])) {
            $instance->pdoCharset = $from[ 'charset' ];
        }
        if (isset($from[ 'collate' ])) {
            $instance->pdoCollate = $from[ 'collate' ];
        }

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

        if (! isset($from[ 'driver' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[driver]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'port' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[port]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'username' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[username]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'password' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[password]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'database' ])) {
            return Ret::throw(
                $fallback,
                [ 'The `from[database]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $pdoHost = $from[ 'host' ] ?? '127.0.0.1';
        $pdoOptions = $from[ 'options' ] ?? [];

        $instance = new static();

        $instance->pdoHost = $pdoHost;
        $instance->pdoOptions = $pdoOptions;

        $instance->pdoDriver = $from[ 'driver' ];
        $instance->pdoPort = $from[ 'port' ];
        $instance->pdoUsername = $from[ 'username' ];
        $instance->pdoPassword = $from[ 'password' ];
        $instance->pdoDatabase = $from[ 'database' ];

        if (isset($from[ 'charset' ])) {
            $instance->pdoCharset = $from[ 'charset' ];
        }
        if (isset($from[ 'collate' ])) {
            $instance->pdoCollate = $from[ 'collate' ];
        }

        return Ret::val($fallback, $instance);
    }


    public function newPdoFromDsn() : \PDO
    {
        $theType = Lib::$type;

        $pdoDsn = $this->pdoDsn;
        $pdoUsername = $this->pdoUsername;
        $pdoPassword = $this->pdoPassword;
        $pdoDatabase = $this->pdoDatabase;
        $pdoOptions = $this->pdoOptions;

        $pdoDsnValid = $theType->string_not_empty($pdoDsn)->orThrow();
        $pdoUsernameValid = $theType->string_not_empty($pdoUsername)->orThrow();
        $pdoPasswordValid = $theType->string($pdoPassword)->orThrow();

        if (null !== $pdoDatabase) {
            $pdoDatabaseValid = $theType->string_not_empty($pdoDatabase)->orThrow();

            $pdoDsnValid .= ";dbname={$pdoDatabaseValid}";
        }

        try {
            $pdo = new \PDO(
                $pdoDsnValid,
                $pdoUsernameValid,
                $pdoPasswordValid,
                $pdoOptions
            );
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException(
                [ 'Unable to ' . __METHOD__, $this ], $e
            );
        }

        $this->sqlEnsureOptions($pdo);

        $sql = implode(";\n", [
            $this->sqlEnsureCharset($pdo),
        ]);

        if ('' !== $sql) {
            $pdo->exec($sql);
        }

        return $pdo;
    }

    public function newPdoFromConfig() : \PDO
    {
        $theType = Lib::$type;

        $pdoDriver = $this->pdoDriver;
        $pdoHost = $this->pdoHost;
        $pdoUsername = $this->pdoUsername;
        $pdoPassword = $this->pdoPassword;
        $pdoPort = $this->pdoPort;
        $pdoDatabase = $this->pdoDatabase;
        $pdoOptions = $this->pdoOptions ?? [];

        $pdoDriverValid = $theType->string_not_empty($pdoDriver)->orThrow();
        $pdoHostValid = $theType->string_not_empty($pdoHost)->orThrow();
        $pdoUsernameValid = $theType->string_not_empty($pdoUsername)->orThrow();
        $pdoPasswordValid = $theType->string($pdoPassword)->orThrow();

        $pdoDsn = "{$pdoDriverValid}:host={$pdoHostValid}";

        if (null !== $pdoPort) {
            $pdoPortValid = $theType->numeric_int_positive($pdoPort)->orThrow();

            $pdoDsn .= ";port={$pdoPortValid}";
        }

        if (null !== $pdoDatabase) {
            $pdoDatabaseValid = $theType->numeric_int_positive($pdoDatabase)->orThrow();

            $pdoDsn .= ";dbname={$pdoDatabaseValid}";
        }

        try {
            $pdo = new \PDO(
                $pdoDsn,
                $pdoUsernameValid,
                $pdoPasswordValid,
                $pdoOptions
            );
        }
        catch ( \Throwable $e ) {
            throw new RemoteException(
                [ 'Unable to ' . __METHOD__, $this ], $e
            );
        }

        $this->sqlEnsureOptions($pdo);

        $sql = implode(";\n", [
            $this->sqlEnsureCharset($pdo),
        ]);

        if ('' !== $sql) {
            $pdo->exec($sql);
        }

        return $pdo;
    }


    public function getPdo() : \PDO
    {
        if (! $this->isPdoInitialized) {
            if (null !== $this->pdo) {
                $pdo = $this->pdo;

                $this->sqlEnsureOptions($pdo);

                $sql = implode(";\n", [
                    $this->sqlEnsureCharset($pdo),
                    $this->sqlEnsureDatabase($pdo),
                ]);

                if ('' !== $sql) {
                    $pdo->exec($sql);
                }

            } else {
                $pdo = null
                    ?? ((null !== $this->pdoDsn) ? $this->newPdoFromDsn() : null)
                    ?? ((null !== $this->pdoHost) ? $this->newPdoFromConfig() : null);
            }

            if (null === $pdo) {
                throw new RemoteException(
                    [ 'Unable to ' . __METHOD__, $this ]
                );
            }

            $this->pdo = $pdo;

            $this->pdoDsn = null;
            $this->pdoDatabase = null;
            $this->pdoHost = null;
            $this->pdoPort = null;
            $this->pdoUsername = null;
            $this->pdoPassword = null;
            $this->pdoCharset = null;
            $this->pdoCollate = null;
            $this->pdoOptions = null;

            $this->isPdoInitialized = true;
        }

        return $this->pdo;
    }


    protected function sqlEnsureOptions(\PDO $pdo) : void
    {
        // > exceptions
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // > return \stdClass
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

        // > $pdo->prepare() on PHP level instead of sending it to MySQL
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        // > since (PHP_VERSION_ID > 80100) mysql integers return integer
        // > setting ATTR_STRINGIFY_FETCHES flag to TRUE forces returning numeric string without casting
        $pdo->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, true);
    }


    protected function sqlEnsureCharset(\PDO $pdo) : string
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = '';

        if ('mysql' === $driverName) {
            $sql = $this->sqlEnsureCharsetMysql();
        }

        return $sql;
    }

    protected function sqlEnsureCharsetMysql() : string
    {
        // > until (PHP_VERSION_ID < 50306) this command was not sent on connect
        // > actually it have to be done using \PDO::MYSQL_ATTR_INIT_COMMAND, but it supports only one query

        $pdoCharset = (string) $this->pdoCharset;
        $pdoCollate = (string) $this->pdoCollate;

        $hasCharset = ('' !== $pdoCharset);
        $hasCollate = ('' !== $pdoCollate);

        $sql = '';

        if ($hasCharset || $hasCollate) {
            if ($hasCharset && $hasCollate) {
                $sql = ''
                    . "SET CHARACTER SET {$pdoCharset};"
                    . "\n" . "SET NAMES {$pdoCharset} COLLATE {$pdoCollate};"
                    . "\n" . "SET collation_connection = \"{$pdoCollate}\";";

            } elseif ($hasCharset) {
                $sql = ''
                    . "\n" . "SET CHARACTER SET {$pdoCharset};"
                    . "\n" . "SET NAMES {$pdoCharset};";

            } elseif ($hasCollate) {
                $sql = ''
                    . "SET collation_connection = \"{$pdoCollate}\";";
            }

            $sql = rtrim($sql, ';');
        }

        return $sql;
    }


    protected function sqlEnsureDatabase(\PDO $pdo) : string
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = '';

        if ('mysql' === $driverName) {
            $sql = $this->sqlEnsureDatabaseMysql();
        }

        return $sql;
    }

    protected function sqlEnsureDatabaseMysql() : string
    {
        $pdoDatabase = (string) $this->pdoDatabase;

        $sql = '';

        if ('' !== $pdoDatabase) {
            $sql = ''
                . "USE {$pdoDatabase};";

            $sql = rtrim($sql, ';');
        }

        return $sql;
    }
}
