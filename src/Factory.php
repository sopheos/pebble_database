<?php

namespace Pebble\Database;

use PDO;
use PDOException;
use Pebble\Database\Exception;

/**
 * Factory
 *
 * @author Mathieu
 */
class Factory
{
    /**
     * @param array $config
     * @return PDO
     * @throws Exception
     */
    public static function create(string $dsn, $username = null, $password = null, array $options = []): PDO
    {
        // Default options
        $options[PDO::ATTR_ERRMODE] = $options[PDO::ATTR_ERRMODE] ?? PDO::ERRMODE_EXCEPTION;
        $options[PDO::ATTR_EMULATE_PREPARES] = $options[PDO::ATTR_EMULATE_PREPARES] ?? false;
        $options[PDO::ATTR_STRINGIFY_FETCHES] = $options[PDO::ATTR_STRINGIFY_FETCHES] ?? false;

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $ex) {
            throw new Exception("Connection ({$dsn})", Exception::CONNECT, $ex);
        }
    }

    // -------------------------------------------------------------------------
}

/* End of file */
