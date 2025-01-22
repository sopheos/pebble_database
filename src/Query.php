<?php

namespace Pebble\Database;

/**
 * Query
 *
 * @author Mathieu
 */
class Query implements QueryInterface
{
    private string $statement;
    private array $data;

    /**
     * @param string $statement
     * @param array $data
     */
    public function __construct(string $statement = '', array $data = [])
    {
        $this->setStatement($statement);
        $this->setData($data);
    }

    /**
     * @param string $statement
     * @return static
     */
    public function setStatement(string $statement = ''): static
    {
        $this->statement = rtrim(trim($statement), ";") . ";";
        return $this;
    }

    /**
     * @param array $data
     * @return static
     */
    public function setData(array $data = []): static
    {
        $this->data = $data;
        return $this;
    }

    public function union(Query $query, bool $all = false): static
    {
        $this->setStatement(
            $this->trimStatement()
                . ($all ? "\nUNION ALL\n" : "\nUNION\n")
                . $query->trimStatement()
                . ';'
        );

        $this->setData(array_merge($this->getData(), $query->getData()));

        return $this;
    }

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }

    public function trimStatement(): string
    {
        return mb_substr($this->statement, 0, -1);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function __toString()
    {
        $query = $this->statement;

        foreach ($this->data as $v) {
            $replace = self::wrapText(self::escape($v));
            $pos = strpos($query, '?');
            if ($pos !== false) {
                $query = substr_replace($query, $replace ?? '', $pos, 1);
            }
        }

        return $query;
    }

    public static function wrapText(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([static::class, 'wrapText'], $value);
        }

        if (is_string($value)) {
            return "'{$value}'";
        }

        return $value;
    }

    public static function escape(mixed $value): mixed
    {
        if (! $value) {
            return $value;
        }

        if (is_array($value)) {
            return array_map([static::class, 'escape'], $value);
        }

        if (is_string($value)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
        }

        return $value;
    }
}
