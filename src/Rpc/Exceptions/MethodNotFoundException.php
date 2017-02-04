<?php

namespace BostjanOb\QueuePlatform\Rpc\Exceptions;

class MethodNotFoundException extends RpcException
{
    protected $code = -32601;
    protected $message = 'Method not found';
}