<?php

namespace BostjanOb\QueuePlatform\Rpc\Exceptions;

class ParseException extends RpcException
{
    protected $code = -32700;
    protected $message = 'Parse error';
}