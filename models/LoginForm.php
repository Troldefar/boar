<?php

/*******************************
 * Bootstrap LoginForm 
 * AUTHOR: RE_WEB
 * @package app\models\LoginForm
*/

namespace app\models;

use app\core\Model;
use app\core\Application;

class LoginForm extends Model {

    public string $email = '';
    public string $password = '';

    public function login() {
        $user = User::findOne(['email' => $this->email], 'Users');
        if (!$user || !$this->verifyPasswords($this->password, $user->password)) {
            $this->setError('email', 'Email error');
            $this->setError('password', 'Password error');
            return false;
        }
        return Application::$app->authentication->login($user);
    }

    public function verifyPasswords(string $haystack, string $needle): bool {
        return password_verify($haystack, $needle);
    }

    public function labels(): array {
        return [
            'email' => 'Email',
            'password' => 'Password'
        ];
    }

    public function rules(): array {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_VALID_EMAIL],
            'password' => [self::RULE_REQUIRED]
        ];
    }

}