<?php

namespace Pebble\Database;

use PDOException;

/**
 * QBException
 *
 * Database exception class
 *
 * @author Mathieu
 */
class Exception extends \Exception
{
    const CONNECT = 1;
    const PREPARE = 2;
    const BIND = 3;
    const EXECUTE = 4;
    const TRANSACTION = 5;

    /**
     * @param PDOException $ex
     * @return static
     */
    public static function connect(PDOException $ex): static
    {
        return new static(self::pdoMessage($ex), self::CONNECT, $ex);
    }

    /**
     * @param PDOException $ex
     * @return static
     */
    public static function prepare(PDOException $ex): static
    {
        return new static(self::pdoMessage($ex), self::PREPARE, $ex);
    }

    /**
     * @param PDOException $ex
     * @return static
     */
    public static function bind(PDOException $ex): static
    {
        return new static(self::pdoMessage($ex), self::BIND, $ex);
    }

    /**
     * @param PDOException $ex
     * @return static
     */
    public static function execute(PDOException $ex): static
    {
        return new static(self::pdoMessage($ex), self::EXECUTE, $ex);
    }

    /**
     * @param PDOException $ex
     * @return static
     */
    public static function transaction(PDOException $ex): static
    {
        return new static(self::pdoMessage($ex), self::TRANSACTION, $ex);
    }

    // -------------------------------------------------------------------------

    private static function pdoMessage(PDOException $ex): string
    {
        return $ex->getCode() . ': ' .  $ex->getMessage();
    }

    // -------------------------------------------------------------------------
}
