<?php

namespace Gzhegow\Lib\Connect\Pdo;

use Gzhegow\Lib\Lib;
use Gzhegow\Lib\Modules\Type\Ret;
use Gzhegow\Lib\Exception\RuntimeException;
use Gzhegow\Lib\Exception\Runtime\RemoteException;


class PdoAdapter
{
    /**
     * @var \PDO
     */
    protected $pdoDefault;
    /**
     * @var \PDO[]
     */
    protected $pdoReadList = [];
    /**
     * @var \PDO[]
     */
    protected $pdoWriteList = [];

    /**
     * @var \PDO
     */
    protected $configPdo;
    /**
     * @var array
     */
    protected $configPdoOptionsNew = [];
    /**
     * @var array
     */
    protected $configPdoOptionsBoot = [];

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
    protected $configPort;
    /**
     * @var string
     */
    protected $configSock;
    // protected $configSock = '/var/run/mysqld/mysqld.sock';

    /**
     * @var string
     */
    protected $configDriver;

    /**
     * @var string
     */
    protected $configUsername;
    /**
     * @var string
     */
    protected $configPassword;

    /**
     * @var string|null
     */
    protected $configDatabase;

    /**
     * @var string|null
     */
    protected $configCharset = 'utf8';
    /**
     * @var string|null
     */
    protected $configCollate = 'utf8_unicode_ci';

    /**
     * @var string|null
     */
    protected $configTimezone = '+00:00';

    /**
     * @var array
     */
    protected $configUserOptions = [];

    /**
     * @var array[]
     */
    protected $configReadList = [];
    /**
     * @var array[]
     */
    protected $configWriteList = [];

    /**
     * @var \Closure
     */
    protected $fnSelectPdoRead;
    /**
     * @var \Closure
     */
    protected $fnSelectPdoWrite;

    /**
     * @var \Closure
     */
    protected $fnEnsureOptionsUser;

    /**
     * @var \Closure
     */
    protected $fnSqlEnsureCharsetUser;
    /**
     * @var \Closure
     */
    protected $fnSqlEnsureDatabaseUser;
    /**
     * @var \Closure
     */
    protected $fnSqlEnsureTimezoneUser;


    private function __construct()
    {
        $this->fnSelectPdoRead = [ $this, 'doSelectPdoReadDefault' ];
        $this->fnSelectPdoWrite = [ $this, 'doSelectPdoWriteDefault' ];
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
    public static function fromPdo($from, ?array $fallback = null)
    {
        if ( $from instanceof \PDO ) {
            $instance = new static();
            $instance->configPdo = $from;

            return Ret::ok($fallback, $instance);
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
            0                  => null, // dsn
            1                  => null, // username
            2                  => null, // password
            3                  => null, // pdo_options
            //
            'pdo'              => null,
            'pdo_options_new'  => null,
            'pdo_options_boot' => null,
            //
            'dsn'              => null,
            'host'             => null,
            'port'             => null,
            'sock'             => null,
            //
            'driver'           => null,
            //
            'username'         => null,
            'password'         => null,
            //
            'database'         => null,
            //
            'charset'          => null,
            'collate'          => null,
            //
            'timezone'         => null,
            //
            'user_options'     => null,
            //
            'read'             => null,
            'write'            => null,
        ];

        $dsn = $from[0];
        $username = $from[1];
        $password = $from[2];
        $pdoOptionsNew = $from[3];

        if ( null !== $dsn ) {
            $from['dsn'] = $dsn;

            if ( ! (true
                && (null !== $username)
                && (null !== $password)
            ) ) {
                return Ret::throw(
                    $fallback,
                    [ 'The `from[1]` (`username`) and `from[2]` (`password`) is required if `from[0]` (`dsn`) is present', $from ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $from['username'] = $username;
            $from['password'] = $password;
        }

        if ( null !== $pdoOptionsNew ) {
            $from['pdo_options_new'] = $pdoOptionsNew;
        }

        $pdo = $from['pdo'];
        $pdoOptionsNew = $from['pdo_options_new'] ?? [];
        $pdoOptionsBoot = $from['pdo_options_boot'] ?? [];

        $dsn = $from['dsn'];
        $host = $from['host'];
        $port = $from['port'];
        $sock = $from['sock'];

        $driver = $from['driver'];

        $username = $from['username'];
        $password = $from['password'];

        $database = $from['database'];

        $charset = $from['charset'];
        $collate = $from['collate'];

        $timezone = $from['timezone'];

        $userOptions = $from['user_options'] ?? [];

        $readList = $from['read'];
        $writeList = $from['write'];

        $isPdo = (null !== $pdo);
        $isDsn = (null !== $dsn);
        $isHost = (null !== $host);
        $isSock = (null !== $sock);

        if ( $isPdo ) {
            if ( ! ($pdo instanceof \PDO) ) {
                return Ret::throw(
                    $fallback,
                    [ 'The `pdo` should be instance of: ' . \PDO::class, $pdo ],
                    [ __FILE__, __LINE__ ]
                );
            }

            $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

            $host = null;
            $port = null;
            $sock = null;

        } elseif ( $isDsn ) {
            if ( ! $theType->dsn_pdo($dsn, [ &$dsnParams, &$parseUrl ])->isOk([ &$dsn, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }

            $driver = $from['driver'] ?? $parseUrl['scheme'] ?? null;

            $host = $from['host'] ?? $dsnParams['host'] ?? null;
            $port = $from['port'] ?? $dsnParams['port'] ?? null;
            $database = $from['database'] ?? $dsnParams['dbname'] ?? null;
            $charset = $from['charset'] ?? $dsnParams['charset'] ?? null;
            $sock = $from['sock'] ?? $dsnParams['unix_socket'] ?? null;

        } elseif ( $isHost ) {
            $pdo = null;
            $sock = null;

        } elseif ( $isSock ) {
            $pdo = null;
            $host = null;
            $port = null;

        } else {
            return Ret::throw(
                $fallback,
                [
                    ''
                    . 'The `from` should contain at least one of: '
                    . '(`pdo`) or '
                    . '(`dsn` and `username` and `password`) or '
                    . '(`host` and `port` and `username` and `password`)',
                ],
                [ __FILE__, __LINE__ ]
            );
        }

        if ( $isPdo ) {
            if ( ! $theType->string_not_empty($driver)->isOk([ &$driver, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }

        } else {
            if ( $isDsn || $isHost ) {
                if ( ! $theType->string_not_empty($driver)->isOk([ &$driver, &$ret ]) ) {
                    return Ret::throw($fallback, $ret);
                }

                if ( ! $theType->string_not_empty($host)->isOk([ &$host, &$ret ]) ) {
                    return Ret::throw($fallback, $ret);
                }

                if ( ! $theType->int_positive($port)->isOk([ &$port, &$ret ]) ) {
                    return Ret::throw($fallback, $ret);
                }

            } elseif ( $isSock ) {
                if ( ! $theType->string_not_empty($sock)->isOk([ &$sock, &$ret ]) ) {
                    return Ret::throw($fallback, $ret);
                }
            }

            if ( ! $theType->string_not_empty($username)->isOk([ &$username, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }

            if ( ! $theType->string($password)->isOk([ &$password, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( null !== $database ) {
            if ( ! $theType->string_not_empty($database)->isOk([ &$database, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( null !== $charset ) {
            if ( ! $theType->string_not_empty($charset)->isOk([ &$charset, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( null !== $collate ) {
            if ( ! $theType->string_not_empty($collate)->isOk([ &$collate, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        if ( null !== $timezone ) {
            if ( ! $theType->string_not_empty($timezone)->isOk([ &$timezone, &$ret ]) ) {
                return Ret::throw($fallback, $ret);
            }
        }

        $instance = new static();
        //
        $instance->configPdo = $pdo;
        $instance->configPdoOptionsNew = $pdoOptionsNew;
        $instance->configPdoOptionsBoot = $pdoOptionsBoot;
        //
        $instance->configDsn = $dsn;
        $instance->configHost = $host;
        $instance->configPort = $port;
        $instance->configSock = $sock;
        //
        $instance->configDriver = $driver;
        //
        $instance->configUsername = $username;
        $instance->configPassword = $password;
        //
        $instance->configDatabase = $database;
        //
        $instance->configCharset = $charset;
        $instance->configCollate = $collate;
        //
        $instance->configTimezone = $timezone;
        //
        $instance->configUserOptions = $userOptions;

        $isReadArray = is_array($readList);
        $isWriteArray = is_array($writeList);

        if ( $isReadArray || $isWriteArray ) {
            $configDefault = $instance->getConfigDefault();

            // > `dsn` key is not supported for read/write servers for now
            unset($configDefault['dsn']);

            if ( $isReadArray ) {
                foreach ( $readList as $i => $rConfig ) {
                    if ( ! is_array($rConfig) ) {
                        return Ret::throw(
                            $fallback,
                            [ 'Each of `from[read]` should be array', $from, $rConfig, $i ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( $diff = array_diff_key($rConfig, $configDefault) ) {
                        return Ret::throw(
                            $fallback,
                            [
                                ''
                                . 'The `from[read]` item contains unexpected keys: '
                                . implode('|', array_keys($diff)),
                                //
                                $from,
                                $rConfig,
                                $i,
                            ],
                            [ __FILE__, __LINE__ ]
                        );
                    }
                }

                $instance->configReadList = $readList;
            }

            if ( $isWriteArray ) {
                foreach ( $writeList as $i => $wConfig ) {
                    if ( ! is_array($wConfig) ) {
                        return Ret::throw(
                            $fallback,
                            [ 'Each of `from[write]` should be array', $from, $wConfig, $i ],
                            [ __FILE__, __LINE__ ]
                        );
                    }

                    if ( $diff = array_diff_key($wConfig, $configDefault) ) {
                        return Ret::throw(
                            $fallback,
                            [
                                ''
                                . 'The `from[write]` item contains unexpected keys: '
                                . implode('|', array_keys($diff)),
                                //
                                $from,
                                $wConfig,
                                $i,
                            ],
                            [ __FILE__, __LINE__ ]
                        );
                    }
                }

                $instance->configWriteList = $writeList;
            }
        }

        return Ret::ok($fallback, $instance);
    }


    public function getConfigDefault() : array
    {
        return [
            'pdo'              => $this->configPdo,
            'pdo_options_new'  => $this->configPdoOptionsNew,
            'pdo_options_boot' => $this->configPdoOptionsBoot,
            //
            'dsn'              => $this->configDsn,
            'host'             => $this->configHost,
            'port'             => $this->configPort,
            'sock'             => $this->configSock,
            //
            'driver'           => $this->configDriver,
            //
            'username'         => $this->configUsername,
            'password'         => $this->configPassword,
            'database'         => $this->configDatabase,
            //
            'charset'          => $this->configCharset,
            'collate'          => $this->configCollate,
            //
            'timezone'         => $this->configTimezone,
            //
            'user_options'     => $this->configUserOptions,
        ];
    }


    public function newPdoFromConfig(array $configValid) : \PDO
    {
        $theType = Lib::type();

        $configValid += [
            'pdo'              => null,
            'pdo_options_new'  => null,
            'pdo_options_boot' => null,
            //
            'dsn'              => null,
            'host'             => null,
            'port'             => null,
            'sock'             => null,
            //
            'driver'           => null,
            //
            'username'         => null,
            'password'         => null,
            'database'         => null,
            //
            'charset'          => null,
            'collate'          => null,
            //
            'timezone'         => null,
            //
            'user_options'     => null,
        ];

        if ( null !== $configValid['pdo'] ) {
            $pdo = $configValid['pdo'];

        } else {
            $pdoOptionsNew = $configValid['pdo_options_new'];

            $dsn = $configValid['dsn'];
            $host = $configValid['host'];
            $port = $configValid['port'];
            $sock = $configValid['sock'];

            $driver = $configValid['driver'];

            $username = $configValid['username'];
            $password = $configValid['password'];
            $database = $configValid['database'];

            $charset = $configValid['charset'];

            if ( null !== $sock ) {
                $dsn = "{$driver}:unix_socket={$sock}";

            } elseif ( null !== $dsn ) {
                $theType->dsn_pdo($dsn, [ &$dsnParams ])->orThrow();

            } elseif ( null !== $host ) {
                $dsn = "{$driver}:host={$host}";

            } else {
                throw new RuntimeException(
                    [ 'At least one of `sock` or `host` is required', $configValid ]
                );
            }

            if ( null === $sock ) {
                if ( (null !== $port) && (! isset($dsnParams['port'])) ) {
                    $dsn .= ";port={$host}";
                }
            }

            if ( (null !== $database) && (! isset($dsnParams['dbname'])) ) {
                $dsn .= ";dbname={$database}";
            }
            if ( (null !== $charset) && (! isset($dsnParams['charset'])) ) {
                $dsn .= ";charset={$charset}";
            }

            try {
                $pdo = new \PDO(
                    $dsn,
                    $username,
                    $password,
                    $pdoOptionsNew
                );
            }
            catch ( \Throwable $e ) {
                throw new RemoteException(
                    [ 'Unable to ' . __METHOD__, $this ], $e
                );
            }
        }

        $this->pdoEnsureOptions($pdo, $configValid);

        $sql = implode(";\n", [
            $this->sqlEnsureCharset($pdo, $configValid),
            $this->sqlEnsureDatabase($pdo, $configValid),
            $this->sqlEnsureTimezone($pdo, $configValid),
        ]);

        if ( '' !== $sql ) {
            $pdo->exec($sql);
        }

        return $pdo;
    }


    public function isPdoDefault(?\PDO &$pdo = null) : bool
    {
        $pdo = null;

        if ( null !== $this->pdoDefault ) {
            $pdo = $this->pdoDefault;

            return true;
        }

        return false;
    }

    public function getPdoDefault() : \PDO
    {
        if ( null === $this->pdoDefault ) {
            $configDefault = $this->getConfigDefault();

            $pdo = $this->newPdoFromConfig($configDefault);

            $this->pdoDefault = $pdo;
        }

        return $this->pdoDefault;
    }


    public function getReadConfigs() : array
    {
        $configDefault = $this->getConfigDefault();

        $configs = [];

        foreach ( $this->configReadList as $i => $config ) {
            $configs[$i] = []
                + $config
                + $configDefault;
        }

        return $configs;
    }

    public function getPdoRead() : \PDO
    {
        $i = $this->selectPdoRead();

        if ( -1 === $i ) {
            throw new RuntimeException(
                [ 'The `configReadList` is empty' ]
            );
        }

        if ( ! isset($this->pdoReadList[$i]) ) {
            $configRead = []
                + $this->configReadList[$i]
                + $this->getConfigDefault();

            $pdo = $this->newPdoFromConfig($configRead);

            $this->pdoReadList[$i] = $pdo;
        }

        return $this->pdoReadList[$i];
    }

    public function getPdoReadRandom() : \PDO
    {
        $i = $this->doSelectPdoReadDefault();

        if ( -1 === $i ) {
            throw new RuntimeException(
                [ 'The `configReadList` is empty' ]
            );
        }

        if ( ! isset($this->pdoReadList[$i]) ) {
            $configRead = []
                + $this->configReadList[$i]
                + $this->getConfigDefault();

            $pdo = $this->newPdoFromConfig($configRead);

            $this->pdoReadList[$i] = $pdo;
        }

        return $this->pdoReadList[$i];
    }


    public function getWriteConfigs() : array
    {
        $configDefault = $this->getConfigDefault();

        $configs = [];

        foreach ( $this->configWriteList as $i => $config ) {
            $configs[$i] = []
                + $config
                + $configDefault;
        }

        return $configs;
    }

    public function getPdoWrite() : \PDO
    {
        $i = $this->selectPdoWrite();

        if ( -1 === $i ) {
            throw new RuntimeException(
                [ 'The `configReadList` is empty' ]
            );
        }

        if ( ! isset($this->pdoWriteList[$i]) ) {
            $configWrite = []
                + $this->configWriteList[$i]
                + $this->getConfigDefault();

            $pdo = $this->newPdoFromConfig($configWrite);

            $this->pdoWriteList[$i] = $pdo;
        }

        return $this->pdoWriteList[$i];
    }

    public function getPdoWriteRandom() : \PDO
    {
        $i = $this->doSelectPdoWriteDefault();

        if ( -1 === $i ) {
            throw new RuntimeException(
                [ 'The `configReadList` is empty' ]
            );
        }

        if ( ! isset($this->pdoWriteList[$i]) ) {
            $configWrite = []
                + $this->configWriteList[$i]
                + $this->getConfigDefault();

            $pdo = $this->newPdoFromConfig($configWrite);

            $this->pdoWriteList[$i] = $pdo;
        }

        return $this->pdoWriteList[$i];
    }


    public function selectPdoRead() : int
    {
        $fn = $this->fnSelectPdoRead;

        $i = call_user_func($fn, $this->configReadList, $this);

        return $i;
    }

    /**
     * @return static
     */
    public function setFnSelectPdoRead(?\Closure $fnSelectPdoRead)
    {
        $this->fnSelectPdoRead = $fnSelectPdoRead ?? [ $this, 'doSelectPdoReadDefault' ];

        return $this;
    }

    protected function doSelectPdoReadDefault() : int
    {
        return array_rand($this->configReadList ?? [ -1 => true ]);
    }


    public function selectPdoWrite() : int
    {
        $fn = $this->fnSelectPdoWrite;

        $i = call_user_func($fn, $this->configWriteList, $this);

        return $i;
    }

    /**
     * @return static
     */
    public function setFnSelectPdoWrite(?\Closure $fnSelectPdoWrite)
    {
        $this->fnSelectPdoWrite = $fnSelectPdoWrite ?? [ $this, 'doSelectPdoWriteDefault' ];

        return $this;
    }

    protected function doSelectPdoWriteDefault() : int
    {
        return array_rand($this->configWriteList ?? [ -1 => true ]);
    }


    /**
     * @return static
     */
    public function setFnEnsureOptionsUser(?\Closure $fnEnsureOptionsUser)
    {
        $this->fnEnsureOptionsUser = $fnEnsureOptionsUser;

        return $this;
    }


    /**
     * @return static
     */
    public function setFnSqlEnsureCharsetUser(?\Closure $fnSqlEnsureCharsetUser)
    {
        $this->fnSqlEnsureCharsetUser = $fnSqlEnsureCharsetUser;

        return $this;
    }

    /**
     * @return static
     */
    public function setFnSqlEnsureDatabaseUser(?\Closure $fnSqlEnsureDatabaseUser)
    {
        $this->fnSqlEnsureDatabaseUser = $fnSqlEnsureDatabaseUser;

        return $this;
    }

    /**
     * @return static
     */
    public function setFnSqlEnsureTimezoneUser(?\Closure $fnSqlEnsureTimezoneUser)
    {
        $this->fnSqlEnsureTimezoneUser = $fnSqlEnsureTimezoneUser;

        return $this;
    }


    protected function pdoEnsureOptions(\PDO $pdo, array $configValid) : void
    {
        $this->pdoEnsureOptionsDefault($pdo, $configValid);
        $this->pdoEnsureOptionsBoot($pdo, $configValid);
        $this->pdoEnsureOptionsUser($pdo, $configValid);
    }

    protected function pdoEnsureOptionsDefault(\PDO $pdo, array $configValid) : void
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

    protected function pdoEnsureOptionsBoot(\PDO $pdo, array $configValid) : void
    {
        $pdoOptionsBoot = $configValid['pdo_options_boot'] ?? [];
        if ( [] === $pdoOptionsBoot ) {
            return;
        }

        foreach ( $pdoOptionsBoot as $pdoOpt => $value ) {
            $status = $pdo->setAttribute($pdoOpt, $value);

            if ( false === $status ) {
                throw new RuntimeException(
                    [ 'Unable to set `pdo_options_boot` on \PDO object', $pdoOptionsBoot ]
                );
            }
        }
    }

    protected function pdoEnsureOptionsUser(\PDO $pdo, array $configValid) : void
    {
        $fn = $this->fnEnsureOptionsUser;

        if ( null !== $fn ) {
            call_user_func($fn, $pdo, $configValid, $this);
        }
    }


    protected function sqlEnsureCharset(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $sql .= $this->sqlEnsureCharsetDriver($pdo, $configValid);
        $sql .= $this->sqlEnsureCharsetUser($pdo, $configValid);

        return $sql;
    }

    protected function sqlEnsureCharsetDriver(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ( 'mysql' === $driverName ) {
            $sql = $this->sqlEnsureCharsetDriverMysql($pdo, $configValid);
        }

        return $sql;
    }

    protected function sqlEnsureCharsetDriverMysql(\PDO $pdo, array $configValid) : string
    {
        // > until (PHP_VERSION_ID < 50306) this command was not sent on connect
        // > actually it have to be done using \PDO::MYSQL_ATTR_INIT_COMMAND, but it supports only one query

        $pdoCharset = (string) $configValid['charset'];
        $pdoCollate = (string) $configValid['collate'];

        $hasCharset = ('' !== $pdoCharset);
        $hasCollate = ('' !== $pdoCollate);

        $sql = '';

        if ( $hasCharset || $hasCollate ) {
            if ( $hasCharset && $hasCollate ) {
                $sql = ''
                    . "SET CHARACTER SET {$pdoCharset};"
                    . "\n" . "SET NAMES {$pdoCharset} COLLATE {$pdoCollate};"
                    . "\n" . "SET collation_connection = \"{$pdoCollate}\";";

            } elseif ( $hasCharset ) {
                $sql = ''
                    . "\n" . "SET CHARACTER SET {$pdoCharset};"
                    . "\n" . "SET NAMES {$pdoCharset};";

            } elseif ( $hasCollate ) {
                $sql = ''
                    . "SET collation_connection = \"{$pdoCollate}\";";
            }

            $sql = rtrim($sql, ';');
        }

        return $sql;
    }

    protected function sqlEnsureCharsetUser(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $fn = $this->fnSqlEnsureCharsetUser;

        if ( null !== $fn ) {
            $sql .= call_user_func($fn, $pdo, $configValid, $this);
        }

        return $sql;
    }


    protected function sqlEnsureDatabase(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $sql .= $this->sqlEnsureDatabaseDriver($pdo, $configValid);
        $sql .= $this->sqlEnsureDatabaseUser($pdo, $configValid);

        return $sql;
    }

    protected function sqlEnsureDatabaseDriver(\PDO $pdo, array $configValid) : string
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = '';

        if ( 'mysql' === $driverName ) {
            $sql = $this->sqlEnsureDatabaseDriverMysql($pdo, $configValid);
        }

        return $sql;
    }

    protected function sqlEnsureDatabaseDriverMysql(\PDO $pdo, array $configValid) : string
    {
        $database = (string) $configValid['database'];

        $sql = '';

        if ( '' !== $database ) {
            $sql = ''
                . "USE {$database};";

            $sql = rtrim($sql, ';');
        }

        return $sql;
    }

    protected function sqlEnsureDatabaseUser(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $fn = $this->fnSqlEnsureDatabaseUser;

        if ( null !== $fn ) {
            $sql .= call_user_func($fn, $pdo, $configValid, $this);
        }

        return $sql;
    }


    protected function sqlEnsureTimezone(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $sql .= $this->sqlEnsureTimezoneDriver($pdo, $configValid);
        $sql .= $this->sqlEnsureTimezoneUser($pdo, $configValid);

        return $sql;
    }

    protected function sqlEnsureTimezoneDriver(\PDO $pdo, array $configValid) : string
    {
        $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = '';

        if ( 'mysql' === $driverName ) {
            $sql = $this->sqlEnsureTimezoneDriverMysql($pdo, $configValid);
        }

        return $sql;
    }

    protected function sqlEnsureTimezoneDriverMysql(\PDO $pdo, array $configValid) : string
    {
        $timezone = (string) $configValid['timezone'];

        $sql = '';

        if ( '' !== $timezone ) {
            $sql = ''
                . "SET time_zone = '{$timezone}';";

            $sql = rtrim($sql, ';');
        }

        return $sql;
    }

    protected function sqlEnsureTimezoneUser(\PDO $pdo, array $configValid) : string
    {
        $sql = '';

        $fn = $this->fnSqlEnsureTimezoneUser;

        if ( null !== $fn ) {
            $sql .= call_user_func($fn, $pdo, $configValid, $this);
        }

        return $sql;
    }
}
