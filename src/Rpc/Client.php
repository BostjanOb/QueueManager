<?php

namespace BostjanOb\QueuePlatform\Rpc;

/**
 * Simple JSON-RPC Client
 * @author Bostjan Oblak
 */
class Client
{

    /**
     * @var string
     */
    private $uri;

    /**
     * Client constructor.
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public function call($method, $params = [])
    {

    }

}