<?php

namespace BostjanOb\QueuePlatform\Rpc\Exceptions;

class RpcException extends \Exception
{
    protected $id = null;

    public function setId(?string $id) {
        $this->id = $id;
    }

    public function getId() : ?string
    {
        return $this->id;
    }
}