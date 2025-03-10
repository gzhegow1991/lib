<?php

namespace Gzhegow\Lib\Pdo;

use Gzhegow\Lib\Exception\LogicException;
use Gzhegow\Lib\Exception\RuntimeException;


class PdoAdapter
{
    /**
     * @var string
     */
    protected $pdoDsn;
    /**
     * @var string
     */
    protected $pdoDatabase;

    /**
     * @var string
     */
    protected $pdoUsername;
    /**
     * @var string
     */
    protected $pdoPassword;

    /**
     * @var string
     */
    protected $pdoCharset;
    /**
     * @var string
     */
    protected $pdoCollate;

    /**
     * @var array
     */
    protected $pdoOptions;

    /**
     * @var \PDO
     */
    protected $pdo;


    public function __construct(
        string $pdoDsn = null,
        string $pdoDatabase = null,
        //
        string $pdoUsername = null,
        string $pdoPassword = null,
        //
        string $pdoCharset = null,
        string $pdoCollate = null,
        //
        array $pdoOptions = null
    )
    {
        $pdoCharset = $pdoCharset ?? 'utf8';
        $pdoCollate = $pdoCollate ?? 'utf8_unicode_ci';
        $pdoOptions = []
            + ($pdoOptions ?? [])
            + [
                // > always throw an exception if any error occured
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                //
                // > always return object instead of associative array
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                //
                // > calculate $pdo->prepare() on PHP level instead of sending it to MySQL as is
                \PDO::ATTR_EMULATE_PREPARES   => true,
                //
                // > since (PHP_VERSION_ID > 80100) mysql integers return integer
                // > setting ATTR_STRINGIFY_FETCHES flag to TRUE forces returning numeric string
                \PDO::ATTR_STRINGIFY_FETCHES  => true,
            ];

        if ('' === $pdoDsn) {
            throw new LogicException(
                [ 'The `pdoDsn` should be non-empty string' ]
            );
        }

        if ('' === $pdoUsername) {
            throw new LogicException(
                [ 'The `pdoUsername` should be non-empty string' ]
            );
        }

        $this->pdoDsn = $pdoDsn;
        $this->pdoDatabase = $pdoDatabase;

        $this->pdoUsername = $pdoUsername;
        $this->pdoPassword = $pdoPassword;

        $this->pdoCharset = $pdoCharset;
        $this->pdoCollate = $pdoCollate;

        $this->pdoOptions = $pdoOptions;
    }


    public function hasPdo() : ?\PDO
    {
        return $this->pdo;
    }

    public function getPdo() : \PDO
    {
        if (null === $this->pdo) {
            $pdo = $this->newPdo();

            $this->pdo = $pdo;
            $this->pdoUsername = null;
            $this->pdoPassword = null;
        }

        return $this->pdo;
    }


    /**
     * @return static
     */
    public function setPdo(\PDO $pdo)
    {
        $sql = implode(";\n", [
            $this->sqlEnsureCharset(),
            $this->sqlEnsureDatabase(),
        ]);

        $pdo->exec($sql);

        $this->pdo = $pdo;

        return $this;
    }

    public function newPdo() : \PDO
    {
        $pdoDsn = $this->pdoDsn;
        $pdoDatabase = $this->pdoDatabase;

        if (null !== $this->pdoDatabase) {
            $pdoDsn .= ";dbname={$pdoDatabase}";
        }

        try {
            $pdo = new \PDO(
                $pdoDsn,
                $this->pdoUsername,
                $this->pdoPassword,
                $this->pdoOptions
            );
        }
        catch ( \Throwable $e ) {
            throw new RuntimeException(
                'Unable to connect', $e
            );
        }

        $sql = $this->sqlEnsureCharset();
        $pdo->exec($sql);

        return $pdo;
    }


    protected function sqlEnsureCharset() : string
    {
        $pdoCharset = $this->pdoCharset;
        $pdoCollate = $this->pdoCollate;

        // > until (PHP_VERSION_ID < 50306) this command was not sent on connect
        // > actually it have to be done using \PDO::MYSQL_ATTR_INIT_COMMAND but it supports only one query
        $sql = "
            SET CHARACTER SET {$pdoCharset};
            SET NAMES {$pdoCharset} COLLATE {$pdoCollate};
            SET collation_connection = \"{$pdoCollate}\";
        ";

        return trim(trim($sql), ';');
    }

    protected function sqlEnsureDatabase() : string
    {
        $pdoDatabase = $this->pdoDatabase;

        $sql = "USE {$pdoDatabase};";

        return trim(trim($sql), ';');
    }
}
