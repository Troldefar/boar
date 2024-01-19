<?php

/**
 * Bootstrap Router 
 * AUTHOR: RE_WEB
 * @package app\core\Router
 */

namespace app\core\src;

use \app\core\src\exceptions\NotFoundException;
use \app\core\src\factories\ControllerFactory;
use \app\core\src\miscellaneous\CoreFunctions;

final class Router {

    protected const INDEX_METHOD = 'index';
    
    protected array $path;
    protected string $method;
    protected bool $rootURL;

    public function __construct(Request $request) {
        $this->path = $request->getArguments();
        $this->rootURL = $request->getPath() === '/';
    }

    protected function createController(): void {
        $app = CoreFunctions::app();
        if (empty($this->path) || $this->rootURL) $app->getResponse()->redirect(CoreFunctions::first($app::$anonymousRoutes)->scalar);
        $handler = ucfirst(CoreFunctions::first($this->path)->scalar);
        $handlerMethod = $this->path[1] ?? '';
        $controller = (new ControllerFactory(['handler' => $handler]))->create();
        $app->setParentController($controller);
        $this->method = $handlerMethod === '' || !method_exists($controller, $handlerMethod) ? self::INDEX_METHOD : $handlerMethod;
    }

    protected function runMiddlewares(): void {
        foreach ($this->getApplicationParentController()->getMiddlewares() as $middleware) $middleware->execute();
    }

    protected function setTemplateControllers(): void {
        if (CoreFunctions::app()::isCLI()) return;
        $this->getApplicationParentController()->setChildren(['Header', 'Footer']);
    }

    protected function runController(): void {
        $controller = $this->getApplicationParentController();
        $controller->setChildData();
        $controller->{$this->method}();
    }

    protected function hydrateDOM(): void {
        $controller = $this->getApplicationParentController();
        $controllerData = $controller->getData();
        extract($controllerData, EXTR_SKIP);
        require_once $controllerData['header'];
        require_once $controller->getView();    
        require_once $controllerData['footer'];
    }

    private function getApplicationParentController(): Controller {
        return CoreFunctions::app()->getParentController();
    }

    public function resolve(): void {
        $this->createController();
        $this->runMiddlewares();
        $this->setTemplateControllers();
        $this->runController();
        $this->hydrateDOM();
    }

}
