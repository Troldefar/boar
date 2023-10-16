<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use app\core\Application;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/public/yard.php';

$app = new Application(true);

$app->connection->applyMigrations();