<?php

namespace BostjanOb\QueuePlatform;

/**
 * Interface Worker
 * @package BostjanOb\QueuePlatform
 */
interface Worker
{
    /**
     * @param null $params
     * @return mixed
     */
    public function run($params = null);
}