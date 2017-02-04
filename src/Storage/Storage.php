<?php

namespace BostjanOb\QueuePlatform\Storage;

use BostjanOb\QueuePlatform\Task;

interface Storage
{

    public function get(int $id): ?Task;
    public function getQueued(?array $workers = []): ?Task;
    public function update(Task $task): Task;
    public function add(Task $task): Task;

}