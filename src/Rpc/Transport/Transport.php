<?php

namespace BostjanOb\QueuePlatform\Rpc\Transport;

interface Transport
{
    public function send($uri, $json);
}