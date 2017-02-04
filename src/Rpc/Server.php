<?php

namespace BostjanOb\QueuePlatform\Rpc;

/**
 * Simple JSON-RPC server
 * @author Bostjan Oblak
 */
class Server
{

    /**
     * Server constructor.
     */
    public function __construct($request = '')
    {
    }

    /**
     * Register object to run methods on
     * @param $object
     * @param array $methods Allowed methods
     */
    public function registerObject($object, array $methods = [])
    {

    }

    /**
     * Run server and return response
     */
    public function run() : string
    {

    }
}