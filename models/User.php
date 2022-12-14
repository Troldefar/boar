<?php

/*******************************
 * Bootstrap RegisterModel 
 * AUTHOR: RE_WEB
 * @package app\models\User
*******************************/

namespace app\models;

use app\core\Application;

class User extends UserModel {

    /*
     * User props
     * @var User properties
    */

    public string $email     = '';
    public string $password  = '';
    public int    $status    = Application::STATUS_INACTIVE;
    public string $firstname = '';
    public string $lastname  = '';

    /*
     * Tablename
     * @return string
    */
    
    public function tableName(): string {
        return 'Users';
    }

    /*
     * Primary key
     * @return string
    */

    public function getPrimaryKey(): string {
        return 'UserID';
    }

    /*
     * Register method 
     * @return boolean
    */

    public function save() {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        return parent::save();
    }

    /** 
     * Validation rules for user registration
     * @return array 
    */

    public function rules(): array {
        return [
            'email' => [
                self::RULE_REQUIRED, self::RULE_VALID_EMAIL, 
                [self::RULE_UNIQUE, 'class' => self::class]
            ],
            'firstname' => [self::RULE_REQUIRED],
            'lastname' => [self::RULE_REQUIRED],
            'password' => [
                self::RULE_REQUIRED, 
                [self::RULE_MIN, 'min' => 8], 
                [self::RULE_MAX, 'max' => 255]
            ],
        ];
    }

    public function getAttributes(): array {
        return ['firstname', 'lastname', 'email', 'status', 'password'];
    }

    public function labels(): array {
        return ['email' => 'Email', 'firstname' => 'First Name', 'lastname' => 'Last Name'];
    }

    public function getDisplayName(): string {
        return $this->firstname;
    }

}