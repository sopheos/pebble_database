<?php

namespace Pebble\Database;

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
     * @param string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function connect(string $msg, \Throwable $prev = null)
    {
        return new static($msg, self::CONNECT, $prev);
    }

    /**
     * @param string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function prepare(string $msg, \Throwable $prev = null)
    {
        return new static($msg, self::PREPARE, $prev);
    }

    /**
     * @param string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function bind(string $msg, \Throwable $prev = null)
    {
        return new static($msg, self::BIND, $prev);
    }

    /**
     * @param string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function execute(string $msg, \Throwable $prev = null)
    {
        return new static($msg, self::EXECUTE, $prev);
    }

    /**
     * @param string $msg
     * @param \Throwable $prev
     * @return \static
     */
    public static function transaction(string $msg, \Throwable $prev = null)
    {
        return new static($msg, self::TRANSACTION, $prev);
    }
}

/* End of file */
