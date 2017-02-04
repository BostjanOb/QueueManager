<?php

namespace BostjanOb\QueuePlatform\Rpc\Exceptions;

class InvalidRequestException extends RpcException
{
    protected $code = -32600;
    protected $message = 'Invalid Request';
}