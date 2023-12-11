<?php

/**
 * Bootstrap application
 * AUTHOR: RE_WEB
 * @package app\core
 */

namespace app\core;

use \app\core\database\Connection;
use \app\config\Config;
use \app\utilities\Logger;
use \app\models\SystemEventModel;
use \app\models\SessionModel;
use \app\models\UserModel;

class Application {

    public const UPLOAD_FOLDER = __DIR__.'/uploads/';

    public static string $ROOT_DIR;
    public string $layout = 'main';
    public string $authenticationClass;
    
    public Router $router;
    public Request $request;
    public Response $response;
    public ?Controller $controller = null;
    public Session $session;
    public Cookie $cookie;
    public Connection $connection;
    public View $view;
    public Env $env;
    public Regex $regex;
    public I18n $i18n;
    public Config $config;
    public Logger $logger;

    public static self $app;
    public static $defaultRoute = ['login' => '/auth/login', 'register' => '/auth/signup'];

    public function __construct(bool $applicationIsMigrating) {
        self::$app = $this;
        self::$ROOT_DIR = dirname(__DIR__);

        $this->config      = new Config();
        $this->setupConnection();

        if ($applicationIsMigrating) return;
        
        $this->request   = new Request();
        $this->response  = new Response();
        $this->regex     = new Regex($this->request->getPath());
        $this->router    = new Router();
        $this->session   = new Session();
        $this->cookie    = new Cookie();
        $this->view      = new View();
        $this->env       = new Env();
        $this->logger    = new Logger();

        $this->checkSessionLanguage();
        $this->getSessionUser();
        
        $this->i18n      = new I18n();

        var_dump(applicationUser()->orders()->where(['OrderID' => 13]));
    }

    protected function setupConnection() {
        $database = $this->config->get('database');
        $applicationConfig = [
            'authenticationClass' => UserModel::class,
            'pdo' => [
                'dsn' => $database->dsn,
                'user' => $database->user,
                'password' => $database->password
            ]
        ];

        $this->authenticationClass = $applicationConfig['authenticationClass'];
        $this->connection = new Connection($applicationConfig['pdo']);
    }

    public function checkSessionLanguage() {
        if (!$this->session->get('language'))
            $this->session->set('language', self::$app->config->get('locale')->default);
    }

    public function getSessionUser() {
        $session = (new SessionModel())::search(['Value' => $this->session->get('SessionID'), 'UserID' => $this->session->get('user')]);
        $validSession = !empty($session) && first($session)->exists();
        $loginRoute = self::$defaultRoute['login'];
        if ($loginRoute !== $this->request->getPath() && !$validSession) $this->response->redirect($loginRoute);
        $user = new UserModel();
        return $user::search([$user->getKeyField() => $this->session->get('user')]);
    }

    /**
     * Run the application 
     * Custom exceptions should be written inside \core\exceptions
     * @return void
     */

    public function run(): void {
        try {
            $this->router->resolve();
        } catch (\Throwable $applicationError) {
            $this->logger->log($applicationError);
            $this->setController(new \app\controllers\ErrorController($applicationError));
        }
    }

    public function classCheck(string $class): void {
        if (!class_exists($class)) $this->addSystemEvent(['Invalid class was called: ' . $class]);
    }

    public function globalThrower(string $message): \Exception {
        throw new \Exception($message);
    }

    protected function exceptionCodeHandler($code) {
        if (!is_int($code))
            throw new \Exception('Invalid status code. Must be int, however ' . gettype($code) . ' is provided.');
    }

    public function getController(): Controller {
        return $this->controller;
    }

    public function setController(Controller $controller): void {
        $this->controller = $controller;
    }

    public static function isCLI(): bool {
        return php_sapi_name() === 'cli';     
    }

    public static function isGuest(): bool {
        return is_null(self::$app->session->get('user')) || self::$app->session->get('user') === '';
    }

    public static function isDevSite(): bool {
        return self::$app->config->get('inDevelopment') === true;
    }

    public function addSystemEvent(array $data): void {
        (new SystemEventModel())
            ->set(['Data' => json_encode($data)])
            ->save();
    }
    
}
