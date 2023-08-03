<?php

namespace Pebble\Database;

/**
 * StatementInterface
 *
 * @author Mathieu
 */
interface StatementInterface
{
    /**
     * Bind params
     *
     * @param array $params
     * @return static
     * @throws Exception
     */
    public function bind(...$params): static;

    // -------------------------------------------------------------------------

    /**
     * Executes a prepared statement
     *
     * @param array $data
     * @return static
     * @throws Exception
     */
    public function execute(array $data = []): static;

    // -------------------------------------------------------------------------

    /**
     * Fetch all results
     *
     * @return array
     */
    public function all(): array;

    // -------------------------------------------------------------------------

    /**
     * Fetch next result
     *
     * @return array|null
     */
    public function next(): ?array;

    // -------------------------------------------------------------------------

    /**
     * Fetch first result
     * Return false if there is not exactly one result
     *
     * @return array|null
     */
    public function one(): ?array;

    // -------------------------------------------------------------------------

    /**
     * Number of rows
     *
     * @return int
     */
    public function count(): int;

    // -------------------------------------------------------------------------
}
