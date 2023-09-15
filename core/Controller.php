<?php

/**
 * Bootstrap Controller 
 * AUTHOR: RE_WEB
 * @package app\core\Controller
*/

namespace app\core;

use \app\core\middlewares\Middleware;
use \app\core\exceptions\NotFoundException;

class Controller {

    private const DEFAULT_METHOD = 'index';
    private const INVALID_METHOD_TEXT = 'Invalid method';
    private const INVALID_CONTROLLER_TEXT = 'Invalid controller';
    private const PARTIALS_TEXT = '/views/partials/';

    /**
     * @var string $currentAction
    */

    public string $action = '';

    /**
     * @var array Variable data generated by extending controllers.
    */

    protected $data = [];

    /*
     * Default layout
    */

    public string $layout = 'main';

    /*
     * Support for additional controller logic, partials
    */

    protected array $children = [];

    /**
     * Set data in current controller
     * @var array|object [\Controller]
     * @return void
    */

    public function setData(array|object $data): void {
        $merged = array_merge($this->data, $data);
        $this->data = $merged;
    }

    /**
     * get data in current controller
     * @return array
    */

    public function getData(): array {
        return $this->data;
    }
    
    /**
     * Array of middleware classes
     * @var app\core\middlewares\Middleware[]
    */

    protected array $middlewares = [];

    public function setChildren(array $children): void {
      foreach ( $children as $child ) $this->children[] = $child; 
    }

    /**
     * Get data from child
     * Then set data on instantiated controller
     * @param array [strings of to be \app\core\Controller]
     * @param \app\core\controller Parent controller
     * @return void
    */

    public function setChildData(array $childControllers): void {
        foreach ( $childControllers as $childController ) {
            [$controller, $method] = preg_match('/:/', $childController) ? explode(':', $childController) : [$childController, self::DEFAULT_METHOD];
            $cController = '\\app\controllers\\'.$controller.'Controller';
            if (!class_exists($cController)) throw new NotFoundException(self::INVALID_CONTROLLER_TEXT);
            if (!method_exists($cController, $method)) throw new NotFoundException(self::INVALID_METHOD_TEXT);
            $static = new $cController();
            $static->{$method}();
            $static->execChildData();
            Application::$app->controller->setData([$static]);
        }
    }

    public function getView() {
        return $this->view;
    }

    /**
     * Get names of children controllers
     * @return array
    */

    public function getChildren() : array {
        return $this->children;
    }

    public function execChildData() {
      $this->setChildData($this->getChildren(), $this);
    }

    /**
     * @param string template name
     * @return string
    */

    public function getTemplatePath(string $template): string {
        return Application::$ROOT_DIR . self::PARTIALS_TEXT . $template . '.tpl.php';
    }

    /**
     * Render view based on data
     * @return void
    */

    public function render(string $view): void {
        Application::$app->view->renderView();
    }

    /**
     * Set layout for the current controller
     * @return void
    */

    public function setLayout(string $layout): void {
        $this->layout = $layout;
    }

    /**
     * Set middlewares for the current controller
     * @return void
    */

    public function registerMiddleware(Middleware $middleware): void {
        $this->middlewares[] = $middleware;
    }   

    /**
     * @return array
    */

    public function getMiddlewares(): array {
        return $this->middlewares;
    }

}
