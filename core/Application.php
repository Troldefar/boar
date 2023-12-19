<?php

/*
|----------------------------------------------------------------------------
| Bootstrap application
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package none
|
*/

namespace app\core;

use \app\core\database\Connection;
use \app\config\Config;
use \app\controllers\AssetsController;
use \app\utilities\Logger;
use \app\models\SystemEventModel;
use \app\models\SessionModel;
use \app\models\UserModel;

class Application {

    public static string $ROOT_DIR;
    public string $layout = 'main';
    
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public Cookie $cookie;
    public Connection $connection;
    public View $view;
    public Env $env;
    public Regex $regex;
    public I18n $i18n;
    public Config $config;
    public Logger $logger;
    public AssetsController $clientAssets;

    protected ?Controller $controller;

    public static self $app;
    public static $defaultRoute = ['/auth/login', '/auth/signup'];

    public function __construct(bool $applicationIsMigrating) {
        self::$app = $this;
        self::$ROOT_DIR = dirname(__DIR__);

        $this->config      = new Config();
        $this->setupConnection();

        if ($applicationIsMigrating) return;
        
        $this->request      = new Request();
        $this->response     = new Response();
        $this->regex        = new Regex($this->request->getPath());
        $this->router       = new Router();
        $this->session      = new Session();
        $this->cookie       = new Cookie();
        $this->view         = new View();
        $this->env          = new Env();
        $this->logger       = new Logger();
        $this->clientAssets = new AssetsController();

        $this->checkSessionLanguage();
        $this->getSessionUser();
        $this->i18n      = new I18n();
    }

    protected function setupConnection() {
        $database = $this->config->get('database');
        $applicationConfig = ['pdo' => ['dsn' => $database->dsn, 'user' => $database->user, 'password' => $database->password]];
        $this->connection = Connection::getInstance($applicationConfig['pdo']);
    }

    public function checkSessionLanguage() {
        if (!$this->session->get('language')) $this->session->set('language', self::$app->config->get('locale')->default);
    }

    public function getSessionUser() {
        $session = (new SessionModel())::query()->select()->where(['Value' => $this->session->get('SessionID'), 'UserID' => $this->session->get('user')])->run();
        $validSession = !empty($session) && first($session)->exists();
        if (!in_array($this->request->getPath(), self::$defaultRoute) && !$validSession) $this->response->redirect(first(self::$defaultRoute)->scalar);
        $user = new UserModel();
        return $user::query()->select()->where([$user->getKeyField() => $this->session->get('user')])->run();
    }

    public function classCheck(string $class): void {
        if (!class_exists($class)) {
            $this->addSystemEvent(['Invalid class was called: ' . $class]);
            throw new \app\core\exceptions\NotFoundException('Invalid class: ' . $class);
        }
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
        return empty(self::$app->getSessionUser());
    }

    public static function isDevSite(): bool {
        return self::$app->config->get('inDevelopment') === true;
    }

    public function addSystemEvent(array $data): void {
        (new SystemEventModel())->set(['Data' => json_encode($data)])->save();
    }

    public function log(string $message, bool $exit = false): void {
        echo date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
        if ($exit) exit();
    }

    public function run(): void {
        try {
            $this->router->resolve();
        } catch (\Throwable $applicationError) {
            $this->logger->log($applicationError);
            $this->setController(new \app\controllers\ErrorController($applicationError));
        }
    }
    
}
