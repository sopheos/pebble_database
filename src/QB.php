<?php

namespace Pebble\Database;

use InvalidArgumentException;

/**
 * Query builder
 *
 * @author Mathieu
 */
class QB
{
    private array $data_keys = [];
    private array $data_values = [];
    private array $data_raw_keys = [];
    private array $data_raw_values = [];
    private string $from_stmt = "";
    private string $group_stmt = "";
    private int $group_count = 0;
    private int $group_level = 0;
    private array $having_data = [];
    private string $having_stmt = "";
    private bool $is_distinct = false;
    private string $join_stmt = "";
    private int $limit_nb = 0;
    private int $offset_nb = 0;
    private string $order_by = "";
    private string $select_stmt = "";
    private array $where_data = [];
    private string $where_stmt = "";

    // -------------------------------------------------------------------------
    // Construct
    // -------------------------------------------------------------------------

    public function __construct(string $table)
    {
        $this->from_stmt = $table;
    }

    /**
     * @param string $table
     * @return static
     */
    public static function create(string $table): static
    {
        return new static($table);
    }

    // -------------------------------------------------------------------------
    // Select
    // -------------------------------------------------------------------------

    /**
     * Select fields
     *
     * @param string ...$cols
     * @return static
     */
    public function select(string ...$cols): static
    {
        if (!$cols) {
            return $this;
        }

        $this->select_stmt .= ",\n\t" . implode(",\n\t", $cols);

        return $this;
    }

    /**
     * Select distinct
     *
     * @return static
     */
    public function distinct(): static
    {
        $this->is_distinct = true;
        return $this;
    }

    // -------------------------------------------------------------------------
    // Table
    // -------------------------------------------------------------------------

    /**
     * Join
     *
     * @param string $table
     * @param string $cond
     * @return static
     */
    public function join(string $table, string $cond): static
    {
        return $this->_join($table, $cond);
    }

    /**
     * Left join
     *
     * @param string $table
     * @param string $cond
     * @return static
     */
    public function left(string $table, string $cond): static
    {
        return $this->_join($table, $cond, "LEFT");
    }

    /**
     * Right join
     *
     * @param string $table
     * @param string $cond
     * @return static
     */
    public function right(string $table, string $cond): static
    {
        return $this->_join($table, $cond, "RIGHT");
    }

    /**
     * @param string $table
     * @param string $cond
     * @param string $type
     * @return static
     */
    private function _join(string $table, string $cond, $type = ""): static
    {
        $this->join_stmt .= "\n" . trim($type . " JOIN " . $table . " ON " . $cond);
        return $this;
    }

    // -------------------------------------------------------------------------
    // Where
    // -------------------------------------------------------------------------

    /**
     * Where
     *
     * @param string $statement
     * @param  mixed $values
     * @return static
     */
    public function where(string $statement, ...$values): static
    {
        return $this->_where("AND", $statement, $values);
    }

    /**
     * @param string $field
     * @return static
     */
    public function whereNull(string $field): static
    {
        return $this->where($field . ' IS NULL');
    }

    /**
     * @param string $field
     * @return static
     */
    public function whereNotNull(string $field): static
    {
        return $this->where($field . ' IS NOT NULL');
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereEq(string $field, $value): static
    {
        if ($value === null) {
            return $this->whereNull($field);
        }
        return $this->where($field . ' = ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereNot(string $field, $value): static
    {
        if ($value === null) {
            return $this->whereNotNull($field);
        }
        return $this->where($field . ' <> ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereSup(string $field, $value): static
    {
        return $this->where($field . ' > ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereInf(string $field, $value): static
    {
        return $this->where($field . ' < ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereSupEq(string $field, $value): static
    {
        return $this->where($field . ' >= ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereInfEq(string $field, $value): static
    {
        return $this->where($field . ' <= ?', $value);
    }

    /**
     * Or Where
     *
     * @param string $statement
     * @param  mixed $values
     * @return static
     */
    public function orWhere(string $statement, ...$values): static
    {
        return $this->_where("OR", $statement, $values);
    }

    /**
     * @param string $field
     * @return static
     */
    public function orWhereNull(string $field): static
    {
        return $this->orWhere($field . ' IS NULL');
    }

    /**
     * @param string $field
     * @return static
     */
    public function orWhereNotNull(string $field): static
    {
        return $this->orWhere($field . ' IS NOT NULL');
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function orWhereEq(string $field, $value): static
    {
        if ($value === null) {
            return $this->orWhereNull($field);
        }
        return $this->orWhere($field . ' = ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function orWhereNot(string $field, $value): static
    {
        if ($value === null) {
            return $this->orWhereNotNull($field);
        }
        return $this->orWhere($field . ' <> ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function orWhereSup(string $field, $value): static
    {
        return $this->orWhere($field . ' > ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function orWhereInf(string $field, $value): static
    {
        return $this->orWhere($field . ' < ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function orWhereSupEq(string $field, $value): static
    {
        return $this->orWhere($field . ' >= ?', $value);
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function orWhereInfEq(string $field, $value): static
    {
        return $this->orWhere($field . ' <= ?', $value);
    }

    /**
     * Where In
     *
     * @param string|array $col
     * @param array|Query $values
     * @return static
     */
    public function whereIn(string|array $col, array|Query $values): static
    {
        return $this->_in("AND", "IN", $col, $values);
    }

    /**
     * Or Where In
     *
     * @param string|array $col
     * @param array|Query $values
     * @return static
     */
    public function orWhereIn(string|array $col, array|Query $values): static
    {
        return $this->_in("OR", "IN", $col, $values);
    }

    /**
     * Where Not In
     *
     * @param string|array $col
     * @param array|Query $values
     * @return static
     */
    public function whereNotIn(string|array $col, array|Query $values): static
    {
        return $this->_in("AND", "NOT IN", $col, $values);
    }

    /**
     * Or Where Not In
     *
     * @param string|array $col
     * @param array|Query $values
     * @return static
     */
    public function orWhereNotIn(string|array $col, array|Query $values): static
    {
        return $this->_in("OR", "NOT IN", $col, $values);
    }

    /**
     * Where in multiple
     *
     * @param array $fields
     * @param array $rows
     * @return static
     */
    public function whereInMultiple(array $fields, array $rows): static
    {
        return $this->_multiple('AND', $fields, $rows);
    }

    /**
     * Or where in multiple
     *
     * @param array $fields
     * @param array $rows
     * @return static
     */
    public function orWhereInMultiple(array $fields, array $rows): static
    {
        return $this->_multiple('AND', $fields, $rows);
    }

    /**
     * Like
     *
     * @param string $col
     * @param string $value
     * @return static
     */
    public function like(string $col, string $value): static
    {
        return $this->where($col . " LIKE ?", $value);
    }

    /**
     * Or Like
     *
     * @param string $col
     * @param string $value
     * @return static
     */
    public function orLike(string $col, string $value): static
    {
        return $this->orWhere($col . " LIKE ?", $value);
    }

    /**
     * Not Like
     *
     * @param string $col
     * @param string $value
     * @return static
     */
    public function notLike(string $col, string $value): static
    {
        return $this->where($col . " NOT LIKE ?", $value);
    }

    /**
     * Or Not Like
     *
     * @param string $col
     * @param string $value
     * @return static
     */
    public function orNotLike(string $col, string $value): static
    {
        return $this->where($col . " NOT LIKE ?", $value);
    }

    /**
     * Auto where
     *
     * @param string $colStr
     * @param mixed $value
     * @return static
     */
    public function whereAuto(string $colStr, $value): static
    {
        $pattern = "/(and|or)?\s*([a-z0-9_]+)\s*(.*)/i";
        $matches = [];

        if (preg_match($pattern, $colStr, $matches) !== 1) {
            throw new InvalidArgumentException("$colStr format");
        }

        $prefix = mb_strtoupper($matches[1] ?: 'and');
        $prefix = mb_strtoupper($matches[1] ?: 'and');
        $col = $matches[2];
        $operator = mb_strtoupper(($matches[3] ?? null) ?: '=');
        $operator = $operator === '!=' ? '<>' : $operator;


        if (is_array($value)) {
            if (!$value) {
                return $this;
            }

            $operator = match ($operator) {
                '=' => "IN",
                '<>' => "NOT IN",
                default => $operator,
            };
            return $this->_in($prefix, $operator, $col, $value);
        }

        if ($value === null) {
            $operator = match ($operator) {
                '=' => "IS",
                '<>' => "IS NOT",
                default => $operator,
            };

            return $this->_where($prefix, "{$col} {$operator} NULL", []);
        }

        return $this->_where($prefix, "{$col} {$operator} ?", [$value]);
    }

    /**
     * Auto where list
     *
     * @param string $colStr
     * @param mixed $value
     * @return static
     */
    public function whereAutoList(array $where = []): static
    {
        foreach ($where as $col => $value) {
            $this->whereAuto($col, $value);
        }

        return $this;
    }

    /**
     * Group Start
     *
     * @return static
     */
    public function groupStart(): static
    {
        return $this->_group("AND");
    }

    /**
     * Or Group Start
     *
     * @return static
     */
    public function orGroupStart(): static
    {
        return $this->_group("OR");
    }

    /**
     * Group End
     *
     * @return static
     */
    public function groupEnd(): static
    {
        if ($this->group_level > 0) {
            $this->where_stmt .= "\n" . str_repeat("\t", $this->group_level) . ")";
            $this->group_level--;
        }

        return $this;
    }

    /**
     * Where helper
     *
     * @param string $prefix
     * @param string $statement
     * @param array $values
     * @return static
     */
    private function _where(string $prefix, string $statement, array $values): static
    {
        // Add link keyword if not the first element of a group
        if (!$this->where_stmt || $this->group_count === 0) {
            $prefix = "";
        }

        $this->group_count++;

        foreach ($values as $v) {
            $this->where_data[] = $v;
        }

        $this->where_stmt .= "\n"
            . str_repeat("\t", $this->group_level + 1)
            . trim($prefix . " " . $statement);

        return $this;
    }

    /**
     * In helper
     *
     * @param string $prefix
     * @param string $operator
     * @param string|array $col
     * @param array|Query $values
     * @return static
     */
    private function _in(string $prefix, string $operator, string|array $col, array|Query $values): static
    {
        if (!$values) {
            return $this;
        }

        $nCol = 1;
        $stmt = '';
        $data = [];

        if (is_array($col)) {
            $nCol = count($col);
            $col = '(' . join(',', $col) . ')';
        }

        // array
        if (is_array($values)) {
            if ($nCol === 1) {
                $stmt = '?' . str_repeat(",?", count($values) - 1);
                $data = $values;
            } else {
                $subStmt = '?' . str_repeat(",?", $nCol - 1);

                foreach ($values as $value) {
                    $stmt .= ',(' . $subStmt . ')';

                    foreach ($value as $value) {
                        $data[] = $value;
                    }
                }

                $stmt = mb_substr($stmt, 1);
            }
        }
        // Subquery
        elseif ($values instanceof Query) {
            $stmt = mb_substr($values->getStatement(), 0, -1);
            $data = $values->getData();
        }

        // No data or bad type
        if (!$stmt || !$data) {
            return $this;
        }

        return $this->_where($prefix, "{$col} {$operator} ({$stmt})", $data);
    }

    private function _multiple(string $prefix, array $fields, array $rows): static
    {
        $nbFields = count($fields);
        $nbRows = count($rows);

        $repeat = '('  . rtrim(str_repeat('?,', $nbFields), ',') . '),';
        $statement = '(' . join(',', $fields) . ') IN (' . rtrim(str_repeat($repeat, $nbRows), ',') . ')';

        $values = [];
        foreach ($rows as $row) {
            foreach ($row as $value) {
                $values[] = $value;
            }
        }

        return $this->_where($prefix, $statement, $values);
    }

    /**
     * Group helper
     *
     * @param string $prefix
     * @return static
     */
    private function _group(string $prefix): static
    {
        if (!$this->where_stmt || $this->group_count === 0) {
            $prefix = "";
        }

        $this->where_stmt .= "\n" . str_repeat("\t", $this->group_level + 1) . trim($prefix . " (");
        $this->group_count = 0;
        $this->group_level++;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Group & Having
    // -------------------------------------------------------------------------

    /**
     * Group By
     *
     * @param string $cols
     * @return static
     */
    public function groupBy(string ...$cols): static
    {
        if ($cols) {
            $this->group_stmt .= ",\n\t" . join(",\n\t", $cols);
        }

        return $this;
    }

    /**
     * Having
     *
     * @param string $statement
     * @param mixed $values
     * @return static
     */
    public function having(string $statement, ...$values): static
    {
        return $this->_having("AND", $statement, $values);
    }

    /**
     * Or Having
     *
     * @param string $statement
     * @param mixed $values
     * @return static
     */
    public function orHaving(string $statement, ...$values): static
    {
        return $this->_having("OR", $statement, $values);
    }

    /**
     * Having helper
     *
     * @param string $prefix
     * @param string $statement
     * @param array $values
     * @return static
     */
    private function _having(string $prefix, string $statement, array $values): static
    {
        if (!$this->having_stmt) {
            $prefix = "";
        }

        foreach ($values as $v) {
            $this->having_data[] = $v;
        }

        $this->having_stmt .= "\n\t" . trim($prefix . " " . $statement);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Order
    // -------------------------------------------------------------------------

    /**
     * Order
     *
     * @param string $cols
     * @return static
     */
    public function orderBy(string ...$cols): static
    {
        return $this->_order($cols);
    }

    /**
     * Order Asc
     *
     * @param string $cols
     * @return static
     */
    public function orderAsc(string ...$cols): static
    {
        return $this->_order($cols, "ASC");
    }

    /**
     * Order Desc
     *
     * @param string $cols
     * @return static
     */
    public function orderDesc(string ...$cols): static
    {
        return $this->_order($cols, "DESC");
    }

    /**
     * Order helper
     *
     * @param array $cols
     * @param string $suffix
     * @return static
     */
    private function _order(array $cols, string $suffix = ""): static
    {
        if (!$cols) {
            return $this;
        }

        if ($suffix) {
            $suffix = " " . $suffix;
        }

        $this->order_by .= ",\n\t" . implode($suffix . ",\n\t", $cols) . $suffix;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Limit
    // -------------------------------------------------------------------------

    /**
     * Limit
     *
     * @param integer $limit
     * @param integer $offset
     * @return static
     */
    public function limit(int $limit, int $offset = 0): static
    {
        $this->limit_nb = $limit;
        $this->offset_nb = $offset;

        return $this;
    }

    // -------------------------------------------------------------------------
    // Set
    // -------------------------------------------------------------------------

    /**
     * Add data
     *
     * @param string $col
     * @param mixed $value
     * @return static
     */
    public function add(string $col, $value): static
    {
        $this->data_keys[] = $col;
        $this->data_values[] = $value;

        return $this;
    }

    /**
     * Add raw data
     *
     * @param string $col
     * @param mixed $value
     * @return static
     */
    public function addRaw(string $col, $value): static
    {
        $this->data_raw_keys[] = $col;
        $this->data_raw_values[] = $value;

        return $this;
    }

    /**
     * Increment
     *
     * @param string $col
     * @param mixed $value
     * @return static
     */
    public function increment(string $col, $val = 1): static
    {
        return $this->addRaw($col, $col . ' + ' . $val);
    }

    /**
     * Decrement
     *
     * @param string $col
     * @param mixed $value
     * @return static
     */
    public function decrement(string $col, $val = 1): static
    {
        return $this->addRaw($col, $col . ' - ' . $val);
    }

    /**
     * Add set of data
     *
     * @param array $data
     * @return static
     */
    public function addList(array $data): static
    {
        foreach ($data as $k => $v) {
            $this->add($k, $v);
        }

        return $this;
    }

    /**
     * Add set of raw data
     *
     * @param array $data
     * @return static
     */
    public function addListRaw(array $data): static
    {
        foreach ($data as $k => $v) {
            $this->addRaw($k, $v);
        }

        return $this;
    }

    // -------------------------------------------------------------------------
    // Build
    // -------------------------------------------------------------------------

    /**
     * Read
     *
     * @return Query
     */
    public function read(): Query
    {
        $str = $this->_buildSelect()
            . $this->_buildFrom()
            . $this->_buildJoin()
            . $this->_buildWhere()
            . $this->_buildGroupBy()
            . $this->_buildHaving()
            . $this->_buildOrderBy()
            . $this->_buildLimit();

        $data = array_merge($this->where_data, $this->having_data);

        return new Query($str, $data);
    }

    /**
     * Count
     *
     * @return Query
     */
    public function count(): Query
    {
        $str = "SELECT\n\tCOUNT(*) AS sum"
            . $this->_buildFrom()
            . $this->_buildJoin()
            . $this->_buildWhere()
            . $this->_buildGroupBy()
            . $this->_buildHaving()
            . $this->_buildOrderBy()
            . $this->_buildLimit();

        if ($this->group_stmt) {
            $str = "SELECT COUNT(*) as sum FROM (\n" . $str . "\n) as QBCOUNT";
        }

        $data = array_merge($this->where_data, $this->having_data);

        return new Query($str, $data);
    }

    /**
     * Insert
     *
     * @param bool $ignore
     * @return Query
     */
    public function insert(bool $ignore = false): Query
    {
        $str = $this->_buildInsert(false, $ignore);
        $data = $this->data_values;

        return new Query($str, $data);
    }

    /**
     * Replace
     *
     * @return Query
     */
    public function replace(): Query
    {
        $str = $this->_buildInsert(true);
        $data = $this->data_values;

        return new Query($str, $data);
    }

    /**
     * Update
     *
     * @param bool $ignore
     * @return Query
     */
    public function update(bool $ignore = false): Query
    {
        $str = $this->_buildUpdate($ignore);
        $data = array_merge($this->data_values, $this->where_data);

        return new Query($str, $data);
    }

    /**
     * Delete
     *
     * @return Query
     */
    public function delete(): Query
    {
        $str = $this->_buildDelete()
            . $this->_buildJoin()
            . $this->_buildWhere()
            . $this->_buildOrderBy()
            . $this->_buildLimit();

        $data = $this->where_data;

        return new Query($str, $data);
    }

    /**
     * Insert all
     *
     * @param array $data
     * @return Query
     */
    public function insertAll(array $data, bool $ignore = false): Query
    {
        return $this->_batch($data, $ignore ? 'INSERT IGNORE' : 'INSERT');
    }

    /**
     * Replace all
     *
     * @param array $data
     * @return Query
     */
    public function replaceAll(array $data): Query
    {
        return $this->_batch($data, 'REPLACE');
    }

    private function _batch(array $data, string $mode): Query
    {
        if (!$data) {
            return new Query();
        }

        $keys = array_keys($data[0]);
        $frag = "(" . join(",", array_fill(0, count($keys), "?")) . ")";

        $query_str = $mode . " INTO ";
        $query_str .= $this->from_stmt . " (" . join(",", $keys) . ") VALUES \n";

        $query_data = [];

        foreach ($data as $sub) {
            $query_str .= $frag . ",\n";
            foreach (array_values($sub) as $v) {
                $query_data[] = $v;
            }
        }

        return new Query(mb_substr($query_str, 0, -2), $query_data);
    }

    /**
     * @return string
     */
    private function _buildSelect(): string
    {
        if (!$this->select_stmt) {
            $this->select("*");
        }

        $distinct = $this->is_distinct ? " DISTINCT" : "";

        return "SELECT" . $distinct . mb_substr($this->select_stmt, 1);
    }

    /**
     * @return string
     */
    private function _buildFrom(): string
    {
        return $this->from_stmt ? "\nFROM " . $this->from_stmt : "";
    }

    /**
     * @return string
     */
    private function _buildJoin(): string
    {
        return $this->join_stmt;
    }

    /**
     * @return string
     */
    private function _buildWhere(): string
    {
        if (!$this->where_stmt) {
            return "";
        }

        while ($this->group_level > 0) {
            $this->groupEnd();
        }

        return "\nWHERE" . $this->where_stmt;
    }

    private function _buildGroupBy(): string
    {
        if ($this->group_stmt) {
            return "\nGROUP BY" . mb_substr($this->group_stmt, 1);
        }

        return "";
    }

    private function _buildHaving(): string
    {
        if ($this->having_stmt) {
            return "\nHAVING" . $this->having_stmt;
        }

        return "";
    }

    private function _buildOrderBy(): string
    {
        if ($this->order_by) {
            return "\nORDER BY" . mb_substr($this->order_by, 1);
        }

        return "";
    }

    private function _buildLimit(): string
    {
        if ($this->limit_nb) {
            $sql = "\nLIMIT " . $this->limit_nb;

            if ($this->offset_nb) {
                $sql .= " OFFSET " . $this->offset_nb;
            }

            return $sql;
        }

        return "";
    }

    private function _buildInsert(bool $replace = false, bool $ignore = false): string
    {
        $keys = "";
        $values = "";
        $sep = ",\n\t\t";

        if ($this->data_keys) {
            $keys .= join($sep, $this->data_keys);
            $values .= mb_substr(str_repeat($sep . "?", count($this->data_keys)), 1);
        }

        if ($this->data_keys && $this->data_raw_keys) {
            $keys .= $sep;
            $values .= $sep;
        }

        if ($this->data_raw_keys) {
            $keys .= join($sep, $this->data_raw_keys);
            $values .= join($sep, $this->data_raw_values);
        }

        $ignore = $ignore && !$replace ? " IGNORE" : "";

        return ($replace ? "REPLACE" : "INSERT" . $ignore)
            . " INTO\n\t" . $this->from_stmt
            . "(\n\t\t" . $keys . "\n\t)\n"
            . "VALUES \t(" . $values . "\n\t)";
    }

    private function _buildUpdate(bool $ignore = false): string
    {
        $ignore = $ignore ? "IGNORE " : "";
        $sql = "UPDATE " . $ignore . $this->from_stmt
            . $this->_buildJoin()
            . "\nSET";

        $update = "";

        foreach ($this->data_keys as $key) {
            $update .= ",\n\t" . $key . " = ?";
        }

        foreach ($this->data_raw_keys as $i => $key) {
            $update .= ",\n\t" . $key . " = " . $this->data_raw_values[$i];
        }

        if ($update) {
            $sql .= mb_substr($update, 1);
        }

        $sql .= $this->_buildWhere();

        return $sql;
    }

    private function _buildDelete(): string
    {
        $select = "";
        if ($this->join_stmt) {
            $from = explode(" ", $this->from_stmt);
            $select = $from[count($from) - 1];
        }

        return "DELETE " . $select . " FROM\n\t" . $this->from_stmt;
    }
}
