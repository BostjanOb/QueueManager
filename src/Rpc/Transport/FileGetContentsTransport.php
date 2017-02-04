<?php

namespace BostjanOb\QueuePlatform\Rpc\Transport;

class FileGetContentsTransport implements Transport
{
    private $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    public function send($uri, $json) {
        $opts = [
            'http' => [
                'method'  => 'GET',
                'header'  => implode("\r\n", $this->headers),
                'content' => $json,
            ],
        ];
        $context = stream_context_create($opts);
        $response = @file_get_contents($uri, false, $context);
        if ($response === false) {
            throw new \Exception('Error connecting to host');
        }

        return $response;
    }
}