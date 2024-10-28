<?php

namespace Pebble\Database;

use InvalidArgumentException;

trait DBServiceTrait
{
    private ?DriverInterface $db = null;

    public function registerDbFactory(DriverInterface $driver)
    {
        $this->db = $driver;
    }

    public function db(): DriverInterface
    {
        if ($this->db === null) {
            throw new InvalidArgumentException(DriverInterface::class . " not registered");
        }

        return $this->db;
    }
}
