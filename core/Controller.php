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


    /**
     * @var string $view
     */

    protected string $view = '';

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

    public function setData($data): void {
      $merged = array_merge($this->getData(), $data);
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
      foreach ( $childControllers as $childKey => $childController ) {
        [$controller, $method] = preg_match('/:/', $childController) ? explode(':', $childController) : [$childController, self::DEFAULT_METHOD];
        $cController = '\\app\controllers\\'.$controller.'Controller';
        $static = new $cController();
        $static->{$method}();
        Application::$app->controller->setData($static->getData());
        $static->execChildData();
      }
    }

    public function getView() {
        return $this->view;
    }


    protected function setView(string $dir, string $view) {
      $this->view = $this->getTemplatePath($dir, $view);
    }

    /**
     * Get names of children controllers
     * @return array
    */

    public function getChildren() : array {
        return $this->children;
    }

    public function execChildData() {
      $this->setChildData($this->getChildren());
    }


    public function getPartialTemplate(string $partial): string {
      return $this->getTemplatePath('partials/', $partial);
    }

    public function getTemplate(string $partial): string {
      return $this->getTemplatePath('', $partial);
    }
    /**
     * @param string template name
     * @return string
    */

    public function getTemplatePath(string $folder, string $template): string {
        return Application::$ROOT_DIR .  '/views/' . $folder . $template . '.tpl.php';
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
