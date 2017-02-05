<?php

namespace BostjanOb\QueuePlatform\Rpc\Transport;

/**
 * Class FileGetContentsTransport
 * @package BostjanOb\QueuePlatform\Rpc\Transport
 */
class FileGetContentsTransport implements Transport
{
    /**
     * Request headers
     * @var array
     */
    private $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    /**
     * Send request and return response
     * @param $uri
     * @param $json
     * @return string
     * @throws \Exception
     */
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