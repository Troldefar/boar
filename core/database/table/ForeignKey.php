<?php

namespace app\core\database\table;

class ForeignKey extends Column {

    protected $foreignTable;
    protected $foreignColumn;
    
    private const DEFAULT_FOREIGN_KEY_COLUMN_TYPE = 'FOREIGN_KEY';
    
    public function __construct(string $name, string $foreignTable, string $foreignColumn) {
        parent::__construct($name, self::DEFAULT_FOREIGN_KEY_COLUMN_TYPE);
        $this->foreignTable = $foreignTable;
        $this->foreignColumn = $foreignColumn;
    }
    
}