<?php

namespace Pebble\Database;

/**
 * QueryInterface
 *
 * @author Mathieu
 */
interface QueryInterface
{
    /**
     * @return string
     */
    public function getStatement(): string;

    /**
     * @return array
     */
    public function getData(): array;

    public function __toString();
}
