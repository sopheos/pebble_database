<?php

namespace Pebble\Database;

use InvalidArgumentException;

trait DBServiceTrait
{
    private ?DriverInterface $db = null;
    private $dbFactory = null;

    public function registerDbFactory(callable $dbFactory)
    {
        $this->dbFactory = $dbFactory;
    }

    public function db(): DriverInterface
    {
        if ($this->db === null) {
            $factory = $this->dbFactory;
            $db = $factory && is_callable($factory) ? $factory() : null;
            if (! ($db instanceof DriverInterface)) {
                throw new InvalidArgumentException("dbFactory does not implements " . DriverInterface::class);
            }

            $this->db = $db;
        }

        return $this->db;
    }
}
