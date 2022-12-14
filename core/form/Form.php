<?php

/*******************************
 * Bootstrap Form 
 * AUTHOR: RE_WEB
 * @package app\core\Form
*/

namespace app\core\form;

use app\core\Model;

class Form {

    public static function begin(string $action, string $method) {
        echo sprintf('<form action="%s" method="%s">', $action, $method);
        return new Form();
    }

    public static function end(): void {
        echo '<button type="submit" class="btn btn-primary">Submit</button></form>';
    }

    public function field(Model $model, string $attribute) {
        return new InputField($model, $attribute);
    }

}