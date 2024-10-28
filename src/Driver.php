<?php

namespace Pebble\Database;

use PDO;
use PDOException;
use Pebble\Database\DriverInterface;
use Pebble\Database\Exception;
use Pebble\Database\QueryInterface;
use Pebble\Database\StatementInterface;

/**
 * Driver
 *
 * @author Mathieu
 */
class Driver implements DriverInterface
{
    private string $dsn;
    private ?string $username = null;
    private ?string $password = null;
    private array $options = [];

    private int $maxReconnectTries;
    private int $reconnectDelay; // in ms

    private static $reconnectErrors = [
        1317, // interrupted
        2002, // refused
        2006, // gone away
    ];

    private $reconnectTries = 0;

    protected ?PDO $connection = null;

    // -------------------------------------------------------------------------

    public function __construct(string $dsn, $username = null, $password = null, array $options = [])
    {
        $default = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        // Default options
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $default + $options;

        $this->setMaxReconnectTries();
        $this->setReconnectDelayMs();
    }

    /**
     * @param array $config
     * @return PDO
     * @throws Exception
     */
    public static function create(string $dsn, $username = null, $password = null, array $options = []): static
    {
        return new static($dsn, $username, $password, $options);
    }

    public function setMaxReconnectTries(int $maxReconnectTries = 0): static
    {
        $this->maxReconnectTries = $maxReconnectTries;
        return $this;
    }

    public function setReconnectDelayMs(int $reconnectDelayMs = 1000): static
    {
        $this->reconnectDelay = $reconnectDelayMs * 1000;
        return $this;
    }

    // -------------------------------------------------------------------------

    public function getConnection(): PDO
    {
        echo "retries:" . $this->reconnectTries . PHP_EOL;

        if (!$this->connection) {
            $this->connection = new PDO($this->dsn, $this->username, $this->password, $this->options);
            $this->reconnectTries = 0;
        }

        return $this->connection;
    }

    private function retry(PDOException $ex): bool
    {
        // not disconnected
        if (! in_array($ex->errorInfo[1] ?? null, self::$reconnectErrors)) {
            return false;
        }

        // cannot retry
        if ($this->reconnectTries >= $this->maxReconnectTries) {
            throw Exception::connect($ex);
        }

        $this->connection = null;
        $this->reconnectTries++;
        usleep($this->reconnectDelay);

        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Changes the current database
     *
     * @param string $database
     * @return static
     * @throws Exception
     */
    public function use(string $database): static
    {
        try {
            $this->getConnection()->exec("USE {$database}");
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->use($database) : (throw Exception::execute($ex));
        }

        return $this;
    }

    /**
     * Returns the last inserted id
     *
     * @return integer
     */
    public function getId(): int
    {
        try {
            return $this->getConnection()->lastInsertId() ?: 0;
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->getId() : (throw Exception::execute($ex));
        }
    }

    /**
     * Escapes a string
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str): string
    {
        try {
            return $this->getConnection()->quote($str);
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->getId() : (throw Exception::execute($ex));
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Start a transaction
     *
     * @return static
     * @throws Exception
     */
    public function transaction(): static
    {
        try {
            return $this->getConnection()->beginTransaction();
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->transaction() : (throw Exception::transaction($ex));
        }

        return $this;
    }

    /**
     * Commit a transaction
     *
     * @return static
     * @throws Exception
     */
    public function commit(): static
    {
        try {
            return $this->getConnection()->commit();
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->commit() : (throw Exception::transaction($ex));
        }

        return $this;
    }

    /**
     * Rollback a transaction
     *
     * @return static
     * @throws Exception
     */
    public function rollback(): static
    {
        try {
            return $this->getConnection()->rollback();
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->rollback() : (throw Exception::transaction($ex));
        }

        return $this;
    }

    // -------------------------------------------------------------------------

    /**
     * Do not check foreign key constraints
     *
     * @return StatementInterface
     */
    public function disableFkCheck(): StatementInterface
    {
        return $this->query("SET FOREIGN_KEY_CHECKS=0;");
    }

    /**
     * Check foreign key constraints
     *
     * @return StatementInterface
     */
    public function enableFkCheck(): StatementInterface
    {
        return $this->query("SET FOREIGN_KEY_CHECKS=1;");
    }

    // -------------------------------------------------------------------------

    /**
     * Executes an SQL statement
     *
     * @param string $sql
     * @return StatementInterface
     * @throws Exception
     */
    public function query(string $sql): StatementInterface
    {
        try {
            $stmt = $this->getConnection()->query($sql);
            return new Statement($stmt);
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->query($sql) : (throw Exception::execute($ex));
        }
    }

    /**
     * Prepares a statement for execution
     *
     * @param string $statement
     * @return StatementInterface
     * @throws Exception
     */
    public function prepare(string $statement): StatementInterface
    {
        try {
            $pdoStmt = $this->getConnection()->prepare($statement);
            return Statement::create($pdoStmt);
        } catch (PDOException $ex) {
            return $this->retry($ex) ? $this->prepare($statement) : (throw Exception::prepare($ex));
        }
    }

    /**
     * Executes a SQL statement from a Query object
     *
     * @param QueryInterface $query
     * @return StatementInterface
     * @throws Exception
     */
    public function exec(QueryInterface $query): StatementInterface
    {
        try {
            $pdoStmt = $this->getConnection()->prepare($query->getStatement());
            return Statement::create($pdoStmt)->execute($query->getData());
        } catch (PDOException $ex) {
            if ($this->retry($ex)) {
                return $this->exec($query);
            }
            throw $this->queryException($query, Exception::prepare($ex));
        } catch (Exception $ex) {
            $prev = $ex->getPrevious();
            if ($prev && ($prev instanceof PDOException) && $this->retry($prev)) {
                return $this->exec($query);
            }
            throw $this->queryException($query, $ex);
        }
    }

    private function queryException(QueryInterface $query, Exception $ex): Exception
    {
        return new Exception(
            $ex->getMessage() . "\n" . $query->__toString(),
            $ex->getCode(),
            $ex->getPrevious()
        );
    }

    // -------------------------------------------------------------------------
}
