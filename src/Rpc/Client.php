<?php

namespace BostjanOb\QueuePlatform\Rpc;

use BostjanOb\QueuePlatform\Rpc\Transport\Transport;

/**
 * Simple JSON-RPC Client
 */
class Client
{
    /**
     * @var int
     */
    private static $id = 1;

    /**
     * @var string
     */
    private $uri;
    /**
     * @var Transport
     */
    private $transport;

    /**
     * Client constructor.
     * @param string $uri
     * @param Transport $transport
     */
    public function __construct(string $uri, Transport $transport)
    {
        $this->uri = $uri;
        $this->transport = $transport;
    }

    /**
     * Send request
     *
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function request($method, $params = [])
    {
        $json = $this->generateJson($method, $params);
        return json_decode($this->transport->send($this->uri, $json), true);
    }

    /**
     * Generate request json
     *
     * @param $method
     * @param $params
     * @return string
     */
    private function generateJson($method, $params)
    {
        self::$id = self::$id + 1;

        return json_encode([
            'jsonrpc' => '2.0',
            'id'      => self::$id,
            'method'  => $method,
            'params'  => $params,
        ]);
    }
}
