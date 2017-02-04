<?php
use BostjanOb\QueuePlatform\Task;

class StorageStub implements \BostjanOb\QueuePlatform\Storage\Storage
{

    private $storage = [];

    public function get(int $id): ?Task
    {
        return $this->storage[$id] ?? null;
    }

    public function getQueued(?array $workers = []): ?Task
    {
        // TODO: Implement getQueued() method.
    }

    public function update(Task $task): Task
    {
        $this->storage[$task->getId()] = $task;
        return $task;
    }

    public function add(Task $task): Task
    {
        $task->setId( count($this->storage) );
        $this->storage[] = $task;
        return $task;
    }
}