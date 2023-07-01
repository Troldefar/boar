<?php

/*******************************
 * Authentication mechanism for whatever you need
 * Use this object to authenticate with the application, external api, ect
 * AUTHOR: RE_WEB
 * @package app\models\Authenticator
*/

namespace app\models;

use \app\core\Application;
use \app\core\Curl;

class Authenticator {

    protected array $data;

    public function __construct(array $data, string $method) {
        $this->data = $data;
        if (!method_exists($this, $method)) throw new \Exception('Invalid method');
        $this->$method();
    }

    /**
     * Application authentication mechanism 
     * @return void
    */

    public function login() {
        $status = false;
        $user = UserModel::search(['email' => $this->data['email']]);
        if (!empty($user)) {
            $user = $user[array_key_first($user)];
            $status = password_verify($this->data['password'], $user->get('Password'));
        }
        Application::$app->response->setResponse(200, 'application/json', ['message' => $status]);
    }

    public function api(): void {
        $curl = new Curl($this->data['endpoint']);
        $curl->setUrl($endpoint);
        $curl->setHeaders($this->data['headers']);
        $curl->setData($this->data['data']);
        $curl->send();
        $response =  $curl->content;
        $curl->close();
    }

}