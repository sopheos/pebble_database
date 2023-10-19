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
     * @param PDOException|string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function connect(PDOException|string $msg, \Throwable $prev = null)
    {
        if ($msg instanceof PDOException) {
            $msg = self::pdoMessage($msg);
        }
        return new static($msg, self::CONNECT, $prev);
    }

    /**
     * @param PDOException|string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function prepare(PDOException|string $msg, \Throwable $prev = null)
    {
        if ($msg instanceof PDOException) {
            $msg = self::pdoMessage($msg);
        }
        return new static($msg, self::PREPARE, $prev);
    }

    /**
     * @param PDOException|string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function bind(PDOException|string $msg, \Throwable $prev = null)
    {
        if ($msg instanceof PDOException) {
            $msg = self::pdoMessage($msg);
        }
        return new static($msg, self::BIND, $prev);
    }

    /**
     * @param PDOException|string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function execute(PDOException|string $msg, \Throwable $prev = null)
    {
        if ($msg instanceof PDOException) {
            $msg = self::pdoMessage($msg);
        }
        return new static($msg, self::EXECUTE, $prev);
    }

    /**
     * @param PDOException|string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function transaction(PDOException|string $msg, \Throwable $prev = null)
    {
        if ($msg instanceof PDOException) {
            $msg = self::pdoMessage($msg);
        }
        return new static($msg, self::TRANSACTION, $prev);
    }

    private static function pdoMessage(PDOException $ex): string
    {
        return $ex->getCode() . ': ' .  $ex->getMessage();
    }
}

/* End of file */
