<?php

/*******************************
 * Bootstrap Cookie 
 * AUTHOR: RE_WEB
 * @package app\core\Cookie
*/

namespace app\core;

class Cookie {

    public function setCookie(string $key, string $value): void {
        $_COOKIE[$key] = password_hash($value, PASSWORD_DEFAULT);
    }

    public function getCookie(string $cookie): string {
        return $_COOKIE[$cookie] ?? '';
    }

}