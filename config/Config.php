<?php

/*******************************
 * Bootstrap Config 
 * AUTHOR: RE_WEB
 * @package app\core\Config
*/

namespace app\config;

use \app\core\Application;

class Config {

    public function get(string $key) {
        $config = file_get_contents(Application::$app::$ROOT_DIR.'/static/setup.json', 'R');
        return json_decode($config)->$key ?? 'Key not found';
    }

}