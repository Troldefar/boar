<?php

/**
|----------------------------------------------------------------------------
| MySQL adapter
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package core
|
*/


namespace app\core\src\database\adapters;

use PDO;

class MySQL extends Adapter {

    private array $options = [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ];

    protected string $driverName = 'mysql';

    public function doConnect(object $config): PDO {
        $pdo = new PDO($this->getDriverName() . ':' . $config->dsn, $config->user, $config->password, $this->options);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

}