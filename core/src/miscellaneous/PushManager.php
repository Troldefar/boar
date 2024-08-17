<?php

namespace app\core\src\miscellaneous;

use app\core\src\http\Curl;
use Firebase\JWT\JWT;

class PushManager {
    
    private $vapidPublicKey;
    private $vapidPrivateKey;
    private $subscriptionEndpoint;

    private array $userPayload;

    public function __construct(string $subscriptionEndpoint = null) {
        $this->vapidPublicKey = $this->getVapidPublicKey();
        $this->vapidPrivateKey = app()->getConfig()->get('integrations')->pushAPI->privatePEM;
        $this->subscriptionEndpoint = $subscriptionEndpoint;
    }

    public function setUserPayload(array $data): void {
        $this->userPayload = $data;
    }

    public function getUserPayload(): array {
        return $this->userPayload;
    }

    public function getVapidPublicKey(): string {
        $publicKey = trim(preg_replace('/\-+BEGIN PUBLIC KEY\-+|\-+END PUBLIC KEY\-+|\s+/', '', app()->getConfig()->get('integrations')->pushAPI->b64Public));
        return str_replace(['+', '/', '='], ['-', '_', ''], $publicKey);
    }

    public function getHeaders(): array {
        return [
            'Authorization: WebPush ' . $this->createJWT(),
            'Content-Type: application/octet-stream',
            'Crypto-Key: p256ecdsa=' . $this->vapidPublicKey,
            'TTL: 60'
        ];
    }

    public function sendNotification(): mixed {
        $curl = new Curl();
        $curl->setUrl($this->subscriptionEndpoint);
        $curl->setMethod('POST');
        $curl->setHeaders($this->getHeaders());
        $curl->setData((array)json_encode($this->getFinalPayload()));

        $curl->send();

        return $curl->getContent();
    }

    private function getFinalPayload(): string {
        return json_encode($this->getUserPayload());
    }

    private function createJWT(): string {
        $iat = time();
        $exp = $iat + 3600;

        $payload = [
            'aud' => 'https://fcm.googleapis.com',
            'exp' => $exp,
            'iat' => $iat,
            'sub' => 'mailto:example@example.com' // Change to your email or a relevant contact
        ];

        return JWT::encode($payload, $this->vapidPrivateKey->getFile(), 'ES256');
    }
}
