<?php

namespace Pebble\Database\Models;

class Relation
{
    public string $name;
    public string $db;
    public string $tbl;
    public string $col;
    public string $ref_db;
    public string $ref_tbl;
    public string $ref_col;
    public ?string $key_type;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? '';
        $this->db = $data['db'] ?? '';
        $this->tbl = $data['tbl'] ?? '';
        $this->col = $data['col'] ?? '';
        $this->ref_db = $data['ref_db'] ?? '';
        $this->ref_tbl = $data['ref_tbl'] ?? '';
        $this->ref_col = $data['ref_col'] ?? '';
        $this->key_type = ($data['key_type'] ?? '') ?: null;
    }

    public function label(): string
    {
        return $this->db . '.' . $this->tbl . '.' . $this->col;
    }
}
