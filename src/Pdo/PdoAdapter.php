<?php

namespace Gzhegow\Lib\Pdo;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Php\Result\Result;
use Gzhegow\Lib\Exception\RuntimeException;


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
    protected $pdoHost;
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
     * @return static|bool|null
     */
    public static function from($from, $ctx = null)
    {
        Result::parse($cur);

        $instance = null
            ?? static::fromStatic($from, $cur)
            ?? static::fromPdo($from, $cur)
            ?? static::fromArrayDsn($from, $cur)
            ?? static::fromArrayConfig($from, $cur);

        if ($cur->isErr()) {
            return Result::err($ctx, $cur);
        }

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromStatic($from, $ctx = null)
    {
        if ($from instanceof static) {
            return Result::ok($ctx, $from);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . static::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromPdo($from, $ctx = null)
    {
        if ($from instanceof \PDO) {
            $instance = new static();
            $instance->pdo = $from;

            return Result::ok($ctx, $instance);
        }

        return Result::err(
            $ctx,
            [ 'The `from` should be instance of: ' . \PDO::class, $from ],
            [ __FILE__, __LINE__ ]
        );
    }

    /**
     * @return static|bool|null
     */
    public static function fromArrayDsn($from, $ctx = null)
    {
        if (! (is_array($from) && ([] !== $from))) {
            return Result::err(
                $ctx,
                [ 'The `from` should be non-empty array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'dsn' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[dsn]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $pdoUsername = $from[ 'username' ] ?? $from[ 0 ] ?? null;
        $pdoPassword = $from[ 'password' ] ?? $from[ 1 ] ?? null;

        if (null === $pdoUsername) {
            return Result::err(
                $ctx,
                [ 'The `from[username]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (null === $pdoPassword) {
            return Result::err(
                $ctx,
                [ 'The `from[password]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        $instance->pdoDsn = $from[ 'dsn' ];
        $instance->pdoUsername = $pdoUsername;
        $instance->pdoPassword = $pdoPassword;

        $instance->pdoDatabase = $from[ 'database' ] ?? null;

        if (isset($from[ 'charset' ])) {
            $instance->pdoCharset = $from[ 'charset' ];
        }
        if (isset($from[ 'collate' ])) {
            $instance->pdoCollate = $from[ 'collate' ];
        }

        $instance->pdoOptions = $from[ 'options' ] ?? [];

        return Result::ok($ctx, $instance);
    }

    /**
     * @return static|bool|null
     */
    public static function fromArrayConfig($from, $ctx = null)
    {
        if (! (is_array($from) && ([] !== $from))) {
            return Result::err(
                $ctx,
                [ 'The `from` should be non-empty array', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'driver' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[driver]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'host' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[host]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'port' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[port]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'username' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[username]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'password' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[password]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        if (! isset($from[ 'database' ])) {
            return Result::err(
                $ctx,
                [ 'The `from[database]` is required', $from ],
                [ __FILE__, __LINE__ ]
            );
        }

        $instance = new static();

        $instance->pdoDriver = $from[ 'driver' ];
        $instance->pdoHost = $from[ 'host' ];
        $instance->pdoPort = $from[ 'port' ];
        $instance->pdoUsername = $from[ 'username' ];
        $instance->pdoPassword = $from[ 'password' ];

        $instance->pdoDatabase = $from[ 'database' ];

        $instance->pdoCharset = $from[ 'charset' ] ?? null;
        $instance->pdoCollate = $from[ 'collate' ] ?? null;

        $instance->pdoOptions = $from[ 'options' ] ?? [];

        return Result::ok($ctx, $instance);
    }


    public function newPdoFromDsn() : \PDO
    {
        $pdoDsn = $this->pdoDsn;
        $pdoUsername = $this->pdoUsername;
        $pdoPassword = $this->pdoPassword;

        $pdoDatabase = $this->pdoDatabase;

        $pdoOptions = $this->pdoOptions ?? [];

        if (! (is_string($pdoDsn) && ('' !== $pdoDsn))) {
            throw new RuntimeException(
                [ 'The `this[pdoDsn]` should be non-empty string', $this ]
            );
        }
        if (! (is_string($pdoUsername) && ('' !== $pdoUsername))) {
            throw new RuntimeException(
                [ 'The `this[pdoUsername]` should be non-empty string', $this ]
            );
        }

        if (! (is_string($pdoPassword))) {
            throw new RuntimeException(
                [ 'The `this[pdoPassword]` should be string', $this ]
            );
        }

        if (null !== $pdoDatabase) {
            if (! (is_string($pdoDatabase) && ('' !== $pdoDatabase))) {
                throw new RuntimeException(
                    [ 'The `this[pdoDatabase]` should be non-empty string', $this ]
                );
            }

            $pdoDsn .= ";dbname={$pdoDatabase}";
        }

        try {
            $pdo = new \PDO(
                $pdoDsn,
                $pdoUsername,
                $pdoPassword,
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
        $pdoDriver = $this->pdoDriver;
        $pdoHost = $this->pdoHost;
        $pdoUsername = $this->pdoUsername;
        $pdoPassword = $this->pdoPassword;

        $pdoPort = $this->pdoPort;
        $pdoDatabase = $this->pdoDatabase;

        $pdoOptions = $this->pdoOptions ?? [];

        if (! (is_string($pdoDriver) && ('' !== $pdoDriver))) {
            throw new RuntimeException(
                [ 'The `this[pdoDriver]` should be non-empty string', $this ]
            );
        }
        if (! (is_string($pdoHost) && ('' !== $pdoHost))) {
            throw new RuntimeException(
                [ 'The `this[pdoHost]` should be non-empty string', $this ]
            );
        }
        if (! (is_string($pdoUsername) && ('' !== $pdoUsername))) {
            throw new RuntimeException(
                [ 'The `this[pdoUsername]` should be non-empty string', $this ]
            );
        }

        if (! (is_string($pdoPassword))) {
            throw new RuntimeException(
                [ 'The `this[pdoPassword]` should be string', $this ]
            );
        }

        $pdoDsn = '';

        $pdoDsn = "{$pdoDriver}:host={$pdoHost}";

        if (null !== $pdoPort) {
            if (! (Lib::type()->string_not_empty($pdoPortString, $pdoPort))) {
                throw new RuntimeException(
                    [ 'The `this[pdoPort]` should be non-empty string', $this ]
                );
            }

            $pdoDsn .= ";port={$pdoPort}";
        }

        if (null !== $pdoDatabase) {
            if (! (is_string($pdoDatabase) && ('' !== $pdoDatabase))) {
                throw new RuntimeException(
                    [ 'The `this[pdoPassword]` should be non-empty string', $this ]
                );
            }

            $pdoDsn .= ";dbname={$pdoDatabase}";
        }

        try {
            $pdo = new \PDO(
                $pdoDsn,
                $pdoUsername,
                $pdoPassword,
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
                throw new RuntimeException(
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
        // > always throw an exception if any error occured
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // > always return object instead of associative array
        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);

        // > calculate $pdo->prepare() on PHP level instead of sending it to MySQL as is
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        // > since (PHP_VERSION_ID > 80100) mysql integers return integer
        // > setting ATTR_STRINGIFY_FETCHES flag to TRUE forces returning numeric string
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
        // > actually it have to be done using \PDO::MYSQL_ATTR_INIT_COMMAND but it supports only one query

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
