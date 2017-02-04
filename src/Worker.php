<?php

namespace BostjanOb\QueuePlatform;

interface Worker
{
    public function run($params = null);
}