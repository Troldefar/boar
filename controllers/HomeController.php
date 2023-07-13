<?php

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\Request;
use app\core\Response;
use app\core\middlewares\AuthMiddleware;


class HomeController extends Controller {

    public string $defaultRoute = 'home';

    public function __construct() { 
        
    }

    public function index(Request $request, Response $response) {

        return $this->render($this->defaultRoute, [

        ]);
    }

}