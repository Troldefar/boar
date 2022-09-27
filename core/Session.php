<?php

/*******************************
 * Bootstrap Session 
 * AUTHOR: RE_WEB
 * @package app\core\Session
*/

namespace app\core;

class Session {

    protected const FLASH_ARRAY = 'FLASH_MESSAGES';

    public function __construct() {
        session_start();
        $this->checkFlashMessages();
    }

    public function checkFlashMessages() {
        $flashMessages = $_SESSION[self::FLASH_ARRAY] ?? [];
        foreach ($flashMessages as $flashMessage) $flashMessage['remove'] = true;
        $_SESSION[self::FLASH_ARRAY] = $flashMessages;
        var_dump($_SESSION[self::FLASH_ARRAY]);
    }

    public function setFlashMessage(string $key, string $message) {
        $_SESSION[self::FLASH_ARRAY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlashMessage(string $key) {
        return $_SESSION[self::FLASH_ARRAY][$key]['value'] ?? '';
    }

    public function __destruct() {
        
    }

}