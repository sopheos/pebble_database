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

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
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

        foreach ($this->data as $k => $v) {
            $replace = is_string($v) ? "'" . self::mysql_escape_mimic($v) . "'" : $v;
            $pos = strpos($query, '?');
            if ($pos !== false) {
                $query = substr_replace($query, $replace, $pos, 1);
            }
        }

        return $query;
    }

    /**
     * @param string $inp
     */
    public static function mysql_escape_mimic($inp)
    {
        if (is_array($inp)) {
            return array_map([static::class, 'escape'], $inp);
        }

        if (!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }
}
