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
    private PDO $pdo;

    // -------------------------------------------------------------------------

    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param PDO $pdo
     * @return static
     */
    public static function create(PDO $pdo): static
    {
        return new static($pdo);
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
            $this->pdo->exec("USE {$database}");
        } catch (PDOException $ex) {
            throw Exception::connect($ex);
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
        return $this->pdo->lastInsertId() ?: 0;
    }

    /**
     * Escapes a string
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str): string
    {
        return $this->pdo->quote($str);
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
            $this->pdo->beginTransaction();
        } catch (PDOException $ex) {
            throw Exception::transaction($ex);
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
            $this->pdo->commit();
        } catch (PDOException $ex) {
            throw Exception::transaction($ex);
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
            $this->pdo->rollBack();
        } catch (PDOException $ex) {
            throw Exception::transaction($ex);
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
            $stmt = $this->pdo->query($sql);
            return new Statement($stmt);
        } catch (PDOException $ex) {
            throw Exception::execute($ex);
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
            $stmt = $this->pdo->prepare($statement);
            return new Statement($stmt);
        } catch (PDOException $ex) {
            throw Exception::prepare($ex);
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
            return $this
                ->prepare($query->getStatement())
                ->execute($query->getData());
        } catch (Exception $ex) {
            throw new Exception(
                $ex->getMessage() . "\n" . $query->__toString(),
                $ex->getCode(),
                $ex->getPrevious()
            );
        }
    }

    // -------------------------------------------------------------------------
}
