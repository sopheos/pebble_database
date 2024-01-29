<?php

namespace Pebble\Database\Models;

class Index
{
    public string $name;
    public string $db;
    public string $tbl;
    public string $col;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->db = $data['db'] ?? '';
        $this->tbl = $data['tbl'] ?? '';
        $this->col = $data['col'] ?? '';
    }

    public function isPrimary()
    {
        return $this->name === 'PRIMARY';
    }
}
