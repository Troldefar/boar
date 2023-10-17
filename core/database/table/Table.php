<?php

/**
 * Table builder
 */

namespace app\core\database\table;

class Table {

    protected $name;
    protected $columns = [];

    private const INT_COLUMN_TYPE = 'int';
    private const VARCHAR_COLUMN_TYPE = 'varchar';
    private const TEXT_COLUMN_TYPE = 'text';
    private const PRIMARY_KEY = 'primary_key';

    public function __construct($name) {
        $this->name = $name;
    }

    public function createColumn(string $columnName, string $type, array $options = []) {
        $this->columns[] = new Column($columnName, $type, $options);
    }

    public function increments(string $columnName) {
        $this->createColumn($columnName, self::INT_COLUMN_TYPE, ['AUTO_INCREMENT' => null]);
    }

    public function string(string $columnName, int $length = 75) {
        $this->createColumn($columnName, self::VARCHAR_COLUMN_TYPE, ['LENGTH' => '('.$length.')']);
    }

    public function text(string $columnName) {
        $this->createColumn($columnName, self::TEXT_COLUMN_TYPE);
    }

    public function integer(string $columnName) {
        $this->createColumn($columnName, self::INT_COLUMN_TYPE);
    }

    public function primary(string $columnName) {
        $this->createColumn($columnName, self::PRIMARY_KEY);
    }

    public function foreign(string $columnName, string $foreignTable, string $foreignColumn) {
        $this->columns[] = new ForeignKey($columnName, $foreignTable, $foreignColumn);
    }

    public function getColumns(): array {
        return $this->columns;
    }

    public function getName(): string {
        return $this->name;
    }

}