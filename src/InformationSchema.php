<?php

namespace Pebble\Database;

use Pebble\Database\Models\Index;
use Pebble\Database\Models\Relation;

class InformationSchema
{
    protected DriverInterface $db;

    public function __construct(DriverInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $db
     * @param string $table
     * @param string $col
     * @return Relation[]
     */
    public function searchRelations(string $db, string $table = '', string $col = ''): array
    {
        $qb = QB::create('information_schema.KEY_COLUMN_USAGE as K')
            ->select('K.CONSTRAINT_NAME as name')
            ->select('K.TABLE_SCHEMA as db')
            ->select('K.TABLE_NAME as tbl')
            ->select('K.COLUMN_NAME as col')
            ->select('K.REFERENCED_TABLE_SCHEMA as ref_db')
            ->select('K.REFERENCED_TABLE_NAME as ref_tbl')
            ->select('K.REFERENCED_COLUMN_NAME as ref_col')
            ->select('C.COLUMN_KEY AS key_type')
            ->join('information_schema.COLUMNS AS C', 'C.TABLE_SCHEMA = K.TABLE_SCHEMA AND C.TABLE_NAME = K.TABLE_NAME AND C.COLUMN_NAME = K.COLUMN_NAME')
            ->whereNot('K.REFERENCED_TABLE_SCHEMA', null)
            ->whereEq('K.TABLE_SCHEMA', $db);

        $table ? $qb->whereEq('K.TABLE_NAME', $table) : null;
        $col ? $qb->whereEq('K.COLUMN_NAME', $col) : null;

        return $this->fetch($qb, Relation::class);
    }

    /**
     * @param string $ref_db
     * @param string $ref_table
     * @param string $ref_col
     * @return Relation[]
     */
    public function selectRelations(string $ref_db, string  $ref_table, string $ref_col): array
    {
        $qb = QB::create('information_schema.KEY_COLUMN_USAGE AS K')
            ->select('K.CONSTRAINT_NAME AS name')
            ->select('K.TABLE_SCHEMA AS db')
            ->select('K.TABLE_NAME AS tbl')
            ->select('K.COLUMN_NAME AS col')
            ->select('K.REFERENCED_TABLE_SCHEMA AS ref_db')
            ->select('K.REFERENCED_TABLE_NAME AS ref_tbl')
            ->select('K.REFERENCED_COLUMN_NAME AS ref_col')
            ->select('C.COLUMN_KEY AS key_type')
            ->join('information_schema.COLUMNS AS C', 'C.TABLE_SCHEMA = K.TABLE_SCHEMA AND C.TABLE_NAME = K.TABLE_NAME AND C.COLUMN_NAME = K.COLUMN_NAME')
            ->whereEq('K.REFERENCED_TABLE_SCHEMA', $ref_db)
            ->whereEq('K.REFERENCED_TABLE_NAME', $ref_table)
            ->whereEq('K.REFERENCED_COLUMN_NAME', $ref_col);

        return $this->fetch($qb, Relation::class);
    }

    /**
     * @param Relation $rel
     * @param int $from
     * @param int $to
     * @return int : number of changes
     */
    public function updateRelation(Relation $rel, $from, $to)
    {
        // La colonne est une clé primaire
        if ($rel->key_type === 'PRI') {

            // Colonnes de la clé primaire différents de rel->col
            $primaries = array_filter($this->searchPrimary($rel->db, $rel->tbl), function ($col) use ($rel) {
                return $rel->col !== $col;
            });

            // Clé primaire multiple
            if ($primaries) {

                // On cherche les futurs doublons
                $doublons = $this->db->exec(
                    QB::create($rel->db . '.' . $rel->tbl)
                        ->select(...$primaries)
                        ->whereIn($rel->col, [$from, $to])
                        ->groupBy(...$primaries)
                        ->having('COUNT(*) > 1')
                        ->read()
                )->all();

                // Pour chaque futur doublon on garde uniquement le $to
                foreach ($doublons as $doublon) {
                    $query = QB::create($rel->db . '.' . $rel->tbl);
                    $query->whereEq($rel->col, $from);
                    foreach ($doublon as $k => $v) {
                        $query->whereEq($k, $v);
                    }

                    $this->db->exec($query->delete());
                }
            }
        }

        // Mise a jour from => to
        $query = QB::create($rel->db . '.' . $rel->tbl)
            ->add($rel->col, $to)
            ->whereEq($rel->col, $from)
            ->update();

        return $this->db->exec($query)->count();
    }

    /**
     * @param string $db
     * @param string $table
     * @param string $col
     * @return Index[]
     */
    public function searchIndexes(string $db, string $table = '', string $col = ''): array
    {
        $qb = QB::create('information_schema.STATISTICS')
            ->select('INDEX_NAME as name')
            ->select('TABLE_SCHEMA as db')
            ->select('TABLE_NAME as tbl')
            ->select('COLUMN_NAME as col')
            ->whereEq('TABLE_SCHEMA', $db);

        $table ? $qb->whereEq('TABLE_NAME', $table) : null;
        $col ? $qb->whereEq('COLUMN_NAME', $col) : null;

        return $this->fetch($qb, Index::class);
    }

    // -------------------------------------------------------------------------

    /**
     * Test if a table exists
     *
     * @param string $db
     * @param string $table
     * @return boolean
     */
    public function hasTable(string $db, string $table): bool
    {
        $query = QB::create('information_schema.TABLES')
            ->whereEq('TABLE_SCHEMA', $db)
            ->whereEq('TABLE_NAME', $table)
            ->count();

        $row = $this->db->exec($query)->next();

        return ($row['sum'] ?? 0) === 1;
    }

    public function searchDatabase(string $table): array
    {
        $query = QB::create('information_schema.TABLES')
            ->select('TABLE_SCHEMA as db')
            ->whereEq('TABLE_NAME', $table)
            ->read();

        $res = $this->db->exec($query);

        $out = [];
        while (($row = $res->next())) {
            $out[] = $row['db'];
        }

        return $out;
    }

    public function autoIncrement(string $db, string $table): ?int
    {
        $query = QB::create('information_schema.TABLES')
            ->select('AUTO_INCREMENT as last_id')
            ->whereEq('TABLE_SCHEMA', $db)
            ->whereEq('TABLE_NAME', $table)
            ->read();

        $row = $this->db->exec($query)->next();
        return ($row['last_id'] ?? 0) ?: null;
    }

    public function searchPrimary(string $db, string $table): array
    {
        $query = QB::create('information_schema.COLUMNS')
            ->select('COLUMN_NAME as col')
            ->whereEq('TABLE_SCHEMA', $db)
            ->whereEq('TABLE_NAME', $table)
            ->whereEq('COLUMN_KEY', 'PRI')
            ->read();

        $res = $this->db->exec($query);

        $out = [];
        while (($row = $res->next())) {
            $out[] = $row['col'];
        }

        return $out;
    }

    // -------------------------------------------------------------------------

    protected function fetch(QB $qb, string $classname): array
    {
        $res = $this->db->exec($qb->read());

        $results = [];
        while (($row = $res->next())) {
            $results[] = new $classname($row);
        }

        return $results;
    }

    // -------------------------------------------------------------------------
}
