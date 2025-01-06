<?php

namespace app\core\src\console\cmds;

use \app\core\src\contracts\Console;
use \app\core\src\database\seeders\DatabaseSeeder;

class SeedDatabase implements Console {

    public function run(array $args): void {
        (new DatabaseSeeder())->up(...$args);
    }

}