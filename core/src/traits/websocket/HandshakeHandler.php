<?php

namespace app\core\src\websocket;

class HandshakeHandler {
    
    public function prepareHeaders(string $key): string {
        $headers = "HTTP/1.1 101 Switching Protocols\r\n";
        $headers .= Constants::HEADER_UPGRADE;
        $headers .= Constants::HEADER_CONNECTION_UPGRADE;
        $headers .= Constants::HEADER_WEBSOCKET_VERSION;
        $headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";

        return $headers;
    }

    public function prepareBackendClientHeaders(string $key, object $websocketConfigs): string {
        $request = "GET / HTTP/1.1\r\n";
        $request .= "Host: {$websocketConfigs->address}:{$websocketConfigs->port}\r\n";
        $request .= Constants::HEADER_UPGRADE;
        $request .= Constants::HEADER_CONNECTION_UPGRADE;
        $request .= "Sec-WebSocket-Key: $key\r\n";
        $request .= Constants::HEADER_WEBSOCKET_VERSION;
        $request .= "\r\n";

        return $request;
    }

    public function performHandshake($client) {
        $request = fread($client, 5000);
        Logger::yell("Request received:\n$request\n");

        preg_match(Constants::WEBSOCKET_HEADER_KEY, $request, $matches);

        if (!isset($matches[1])) {
            Logger::yell($request);
            Logger::yell(Constants::WEBSOCKET_HEADER_KEY_NOT_FOUND);
            return false;
        }

        $key = base64_encode(
            pack(
                Constants::PACK_FORMAT_ARG_HEX_ENTIRE_STRING, 
                sha1($matches[1] . app()->getConfig()->get('integrations')->websocket->sha1key)
            )
        );

        $headers = $this->prepareHeaders($key);

        fwrite($client, $headers, strlen($headers));

        Logger::yell("Handshake sent:\n$headers\n");

        return true;
    }
}