<?php

namespace app\core\src\miscellaneous;

class Hash {

    protected const DEFAULT_HASH_LENGTH = 50;

    public static function create(int $length = self::DEFAULT_HASH_LENGTH): string {
        $randomBytes  = random_bytes($length);
        $uniqueString = base64_encode($randomBytes);
        $uniqueString = substr(preg_replace("/[^a-zA-Z0-9]/", "", $uniqueString), 0, $length);
        return $uniqueString;
    }

    public static function uuid(): string {
        return hash('sha256', uniqid());
    }

    public static function createdBasedOn(string $base, int $length = self::DEFAULT_HASH_LENGTH) {
        $b64 = base64_encode($base);
        $uniqueString = substr(preg_replace("/[^a-zA-Z0-9]/", "", $b64), 0, $length);
        return $uniqueString;
    }

}