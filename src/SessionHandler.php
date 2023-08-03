<?php

namespace Pebble\Database;

/**
 * SessionHandler
 *
 * A Session handler using Pebble\Database
 *
 * SQL table sessions :
 * id char(40) NOT NULL,
 * ts int(10) UNSIGNED NOT NULL DEFAULT '0',
 * data text/blob NOT NULL
 *
 * @author Mathieu
 */
class SessionHandler implements \SessionHandlerInterface
{
    private DriverInterface $driver;

    /**
     * @var string
     */
    private $table = 'sessions';

    /**
     * @var string
     */
    private $col_id;

    /**
     * @var string
     */
    private $col_data;

    /**
     * @var string
     */
    private $col_time;

    // -------------------------------------------------------------------------

    /**
     * @param DriverInterface $driver
     * @param array $options
     */
    public function __construct(DriverInterface $driver, array $options = [])
    {
        $this->driver = $driver;
        $this->table = $options['table'] ?? 'sessions';
        $this->col_id = $options['id'] ?? 'id';
        $this->col_time = $options['time'] ?? 'ts';
        $this->col_data = $options['data'] ?? 'data';
    }

    public function open(string $path, string $name): bool
    {
        return $this->driver ? true : false;
    }

    public function read(string $id): string|false
    {
        $query = QB::create($this->table)
            ->whereEq($this->col_id, $id)
            ->read();

        $row = $this->driver->exec($query)->one(false);
        return $row ? $row[$this->col_data] : '';
    }

    // -------------------------------------------------------------------------

    public function write(string $id, string $data): bool
    {
        $query = QB::create($this->table)
            ->add($this->col_id, $id)
            ->add($this->col_time, time())
            ->add($this->col_data, $data)
            ->replace();

        $this->driver->exec($query);

        return true;
    }

    public function destroy(string $id): bool
    {
        $query = QB::create($this->table)
            ->whereEq($this->col_id, $id)
            ->delete();

        $this->driver->exec($query);

        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        $query = QB::create($this->table)
            ->whereInf($this->col_time, time() - $max_lifetime)
            ->delete();

        $res = $this->driver->exec($query);

        return $res->count();
    }
}
