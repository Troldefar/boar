<?php

namespace app\core\src\factories;

use \app\controllers\AssetsController;
use app\controllers\ErrorController;
use \app\core\src\Controller;

class ControllerFactory extends AbstractFactory {

    protected const CONTROLLER_AFFIX = 'Controller';

    public function create(): ?Controller {
        $controller = ('\\app\controllers\\' . $this->getHandler() . self::CONTROLLER_AFFIX);
        if (!$this->validateObject($controller)) return null;
        $app = app();
        return new $controller($app->getRequest(), $app->getResponse(), $app->getSession(), new AssetsController());
    }

}