<?php

namespace Pebble\Database;


interface DriverInterface
{

    // -------------------------------------------------------------------------

    /**
     * Changes the current database
     *
     * @param string $database
     * @return static
     * @throws Exception
     */
    public function use(string $database): static;

    /**
     * Returns the last inserted id
     *
     * @return integer
     */
    public function getId(): int;

    /**
     * Escapes a string
     *
     * @param string $str
     * @return string
     */
    public function escape(string $str): string;

    // -------------------------------------------------------------------------

    /**
     * Start a transaction
     *
     * @return static
     * @throws Exception
     */
    public function transaction(): static;

    /**
     * Commit a transaction
     *
     * @return static
     * @throws Exception
     */
    public function commit(): static;

    /**
     * Rollback a transaction
     *
     * @return static
     * @throws Exception
     */
    public function rollback(): static;

    // -------------------------------------------------------------------------

    /**
     * Do not check foreign key constraints
     *
     * @return StatementInterface
     */
    public function disableFkCheck(): StatementInterface;

    /**
     * Check foreign key constraints
     *
     * @return StatementInterface
     */
    public function enableFkCheck(): StatementInterface;

    // -------------------------------------------------------------------------

    /**
     * Executes an SQL statement
     *
     * @param string $sql
     * @return StatementInterface
     * @throws Exception
     */
    public function query(string $sql): StatementInterface;

    /**
     * Prepares a statement for execution
     *
     * @param Query $statement
     * @return StatementInterface
     * @throws Exception
     */
    public function prepare(string $statement): StatementInterface;

    /**
     * Executes a SQL statement from a Query object
     *
     * @param QueryInterface $query
     * @return StatementInterface
     * @throws Exception
     */
    public function exec(QueryInterface $query): StatementInterface;

    // -------------------------------------------------------------------------

}
