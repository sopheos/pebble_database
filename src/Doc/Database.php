<?php

namespace Pebble\Database\Doc;

use Pebble\Database\DriverInterface;
use Pebble\Database\QB;

class Database
{
    /**
     * @var DriverInterface
     */
    private $db;

    /**
     * @var string
     */
    private $dbname;

    /**
     * @param DriverInterface $db
     * @param string $dbname
     */
    public function __construct(DriverInterface $db, string $dbname)
    {
        $this->db = $db;
        $this->dbname = $dbname;
    }

    /**
     * @param DriverInterface $db
     * @param string $dbname
     * @return \static
     */
    public static function create(DriverInterface $db, string $dbname)
    {
        return new static($db, $dbname);
    }

    /**
     * @return DocTable[]
     */
    public function run()
    {
        $query = QB::create('information_schema.tables')
            ->select('table_name as name')
            ->select('table_comment as comment')
            ->whereEq('table_schema', $this->dbname)
            ->orderAsc('table_name')
            ->read();

        $res = $this->db->exec($query);

        $tables = [];
        while (($row = $res->next())) {
            $table = new DocTable();
            $table->name = $row['name'];
            $table->comment = $row['comment'];
            $table->fields = $this->fields($row['name']);
            $tables[] = $table;
        }

        return $tables;
    }

    /**
     * @param string $name
     * @return DocField[]
     */
    private function fields(string $name)
    {
        $references = $this->references($name);

        $keys = [
            'PRI' => 'primaire',
            'UNI' => 'unique',
            'MUL' => 'multiple'
        ];

        $query = QB::create('information_schema.columns')
            ->select('column_name as name')
            ->select('column_key as idx')
            ->select('column_type as type')
            ->select('column_default as value')
            ->select('is_nullable')
            ->select('extra')
            ->select('column_comment as comment')
            ->whereEq('table_schema', $this->dbname)
            ->whereEq('table_name', $name)
            ->orderAsc('ordinal_position')
            ->read();

        $res = $this->db->exec($query);

        $cols = [];
        while (($col = $res->next())) {

            if ($col['is_nullable'] === 'YES') {
                $value = $col['value'];
            } elseif ($col['value'] === null) {
                $value = $col['extra'] ?: 'NOT NULL';
            } else {
                $value = trim($col['value'] . ' ' . $col['extra']);
            }

            $field = new DocField;
            $field->name = $col['name'];
            $field->idx = $keys[$col['idx']] ?? '';
            $field->type = $col['type'];
            $field->ref =  $references[$col['name']] ?? '';
            $field->value = $value;
            $field->comment = $col['comment'];

            $cols[] = $field;
        }

        return $cols;
    }

    private function references($name)
    {
        $query = QB::create('information_schema.key_column_usage')
            ->select('column_name')
            ->select('referenced_table_name')
            ->select('referenced_column_name')
            ->whereEq('table_schema', $this->dbname)
            ->whereEq('table_name', $name)
            ->whereEq('referenced_table_schema', $this->dbname)
            ->read();

        $res = $this->db->exec($query);

        $refs = [];
        while (($col = $res->next())) {
            $refs[$col['column_name']] = $col['referenced_table_name']
                . '.'
                . $col['referenced_column_name'];
        }

        return $refs;
    }
}
