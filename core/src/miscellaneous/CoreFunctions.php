<?php

/*
|----------------------------------------------------------------------------
| Convenience for those getters that are used frequently throughout the system
|----------------------------------------------------------------------------
|
| @author RE_WEB
| @package app\core\src\miscellaneous
|
*/

namespace app\core\src\miscellaneous;

final class CoreFunctions {

    private function __construct() {}

    public static function displayDD($input, $title = 'Debugging'): void {
        echo '<pre style="padding: 2rem; background-color: #3a6b39; color: white; border-radius: 4px;margin-top: 10px;" class="debug">';
        if ($title) echo '<h2 class="text-center">' . $title . '</h2><hr>';
        var_dump($input);
        self::app()->log('Incident report has been submitted.');
        if ($title) echo '<hr /><h2 class="text-center">End of ' . $title . '</h2></pre>';
    }
      
    public static function dd(mixed $input, $title = ''): void {
        self::displayDD($input, $title);
        exit;
    }
      
    public static function d(mixed $input, $title = ''): void {
        self::displayDD($input, $title);
        echo '<hr />';
    }
      
    public static function hs($input): string {
        return Html::escape($input);
    }
      
    public static function app(): object {
        return \app\core\Application::$app;
    }
      
    public static function validateCSRF(): bool {
        return (new \app\core\src\tokens\CsrfToken())->validate();
    }
      
    public static function nukeSession(): void {
        self::app()->getSession()->nullAll();
    }

    public static function restartSession(): void {
        self::app()->getSession()->restart();
    }
      
    public static function ths(string $input): string {
        return self::hs(self::app()->getI18n()->translate($input));
    }
      
    public static function first(array|object $iterable): object {
        return (object)$iterable[array_key_first($iterable)];
    }
      
    public static function getIndex(array|object $iterable, int|string $expectedIndex): ?object {
          if (!isset($iterable[$expectedIndex])) return (object)['scalar' => 'Invalid'];
          return (object)$iterable[$expectedIndex];
    }
      
    public static function last(array|object $iterable): object {
        return (object)$iterable[array_key_last($iterable)];
    }
      
    public static function loopAndEcho(array|object $iterable, bool $echoKey = false): void {
        foreach ($iterable as $key => $value) echo $echoKey ? $key : $value;
    }
      
    public static function applicationUser(): ?\app\models\UserModel {
        $user = self::app()->getUser();
        return empty($user) ? null : self::first($user);
    } 
}