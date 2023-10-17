<?php

/**
 * Schema modifier
 * @package app\core\database
 * @author RE_WEB
*/

namespace app\core\database;

class Schema {

    public function drop(string $table) {
        $query = ' DROP TABLE IF EXISTS ' . $table;
        app()->connection->rawSQL($query)->execute();
    }

    /**
     * @param string $tablename
     * @param \Closure $callback
     * @return void
     */

    public function create(string $table, \Closure $callback): void {
        $table = new table\Table($table);
        $callback($table);
        $this->createIfNotExists($table);
    }

    public function createIfNotExists(table\Table $table) {
        $query = 'CREATE TABLE IF NOT EXISTS ' . $table->getName() . '(';
        foreach ( $table->getColumns() as $columnKey => $columnOptions )
            $query .= 
                $columnOptions->queryString() . 
                (array_key_last($table->getColumns()) === $columnKey ? null : ', ');
        $query .= ')';
        app()
            ->connection
            ->rawSQL($query)
            ->execute();
    }

}