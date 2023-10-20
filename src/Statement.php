<?php

namespace Pebble\Database;

use PDO;
use PDOException;
use PDOStatement;
use Pebble\Database\Exception;
use Pebble\Database\StatementInterface;

/**
 * Statement
 *
 * @author Mathieu
 */
class Statement implements StatementInterface
{
    private PDOStatement $stmt;

    // -------------------------------------------------------------------------

    /**
     * @param PDOStatement $stmt
     */
    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * @param PDOStatement $stmt
     * @return static
     */
    public static function create(PDOStatement $stmt): static
    {
        return new static($stmt);
    }

    // -------------------------------------------------------------------------

    /**
     * @param [mixed] ...$args
     * @return static
     */
    public function bind(...$args): static
    {
        $this->stmt->bindParam(...$args);
        return $this;
    }

    /**
     * Executes a prepared statement
     *
     * @param array $data
     * @return static
     * @throws Exception
     */
    public function execute(array $data = []): static
    {
        try {
            $this->stmt->execute($data ?: null);
        } catch (PDOException $ex) {
            throw Exception::execute($ex);
        }

        return $this;
    }

    // -------------------------------------------------------------------------

    /**
     * Fetch all results
     *
     * @return array
     */
    public function all(): array
    {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // -------------------------------------------------------------------------

    /**
     * @return array|null
     */
    public function next(): ?array
    {
        return $this->stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // -------------------------------------------------------------------------

    /**
     * Fetch first result
     * Return false if there is not exactly one result
     *
     * @return array|null
     */
    public function one(): ?array
    {
        $all = $this->all();
        return ($all && count($all) === 1) ? $all[0] : null;
    }

    // -------------------------------------------------------------------------

    /**
     * Number of rows
     *
     * @return int
     */
    public function count(): int
    {
        return $this->stmt->rowCount();
    }

    // -------------------------------------------------------------------------
}
