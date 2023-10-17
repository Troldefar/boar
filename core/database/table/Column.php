<?php

namespace app\core\database\table;

class Column {

    protected const PRIMARY_KEY = 'PRIMARY_KEY';

    protected string $name;
    protected string $type;
    protected array  $options = [];
    protected array  $exclude = ['LENGTH'];

    public function __construct(string $name, string $type, array $options = []) {
        $this->name = $name;
        $this->type = $type;
        $this->options = $options;
    }

    public function get(string $key): string|array {
        return $this->{$key} ?? 'Invalid';
    }

    public function queryString(): string {
        $options = '';
        foreach ( $this->get('options') as $optionKey => $option )  $options .= ' ' . (in_array($optionKey, $this->exclude) ? '' : $optionKey) . ' ' . ($option ?? '');
        return strtoupper($this->type) === self::PRIMARY_KEY ? "PRIMARY KEY ($this->name) " : $this->name . ' ' .  strtoupper($this->type) . (count($this->get('options')) ? $options : null);
    }

}