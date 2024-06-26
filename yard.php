<?php

/**
|----------------------------------------------------------------------------
| Session
|----------------------------------------------------------------------------
|
*/

session_start();

/**
|----------------------------------------------------------------------------
| Error reporting
|----------------------------------------------------------------------------
|
*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
|----------------------------------------------------------------------------
| Define
|----------------------------------------------------------------------------
|
*/

define('CRONJOB_CLI_CHECK', 'CronjobScheduler');
define('DATABASE_MIGRATION_CLI_CHECK', 'DatabaseMigration');
define('WEBSOCKET_INIT', 'WebsocketInit');

define('CLI_TOOL_NOT_FOUND_MESSAGE', 'CLI TOOL NOT FOUND' . PHP_EOL);
define('IS_CLI', isset($argv));

/**
|----------------------------------------------------------------------------
| Functions
|----------------------------------------------------------------------------
|
*/

function ths(string $string): string {
    return \app\core\src\miscellaneous\CoreFunctions::ths($string);
}

function hs(?string $string): string {
    if (!$string) return '';

    return htmlspecialchars($string);
}

function app() {
    return \app\core\src\miscellaneous\CoreFunctions::app();
}

function getIterableJsonEncodedData(array|object $iterable): array {
    $result = [];

    foreach ($iterable as $iteration) {
        var_dump($iteration);
        if (!method_exists($iteration, 'getData')) 
            throw new \app\core\src\exceptions\NotFoundException("getData method was not found");
        
        foreach ($iteration->getData() as $dataKey => $dataValue) {
            if (is_iterable($dataValue)) $result[$iteration->key()][$dataKey] = getIterableJsonEncodedData($dataValue);
            else $result[$iteration->key()][$dataKey] = json_encode($dataValue);
        }
    }

    return $result;
}

function debug($data) {
    app()->getLogger()->log($data);
}

function renderComponent($method, $arguments = []) {
    return \app\core\src\html\Html::$method(...$arguments);
}

function panic(string $reason = ''): void {
    exit($reason);
}