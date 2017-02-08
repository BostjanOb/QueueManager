<?php

use BostjanOb\QueuePlatform\Task;

class StorageStub implements \BostjanOb\QueuePlatform\Storage\Storage
{

    private $storage = [];

    public function get(int $id): ?Task
    {
        return $this->storage[$id] ?? null;
    }

    public function pop(?array $workers = []): ?Task
    {
        foreach ($this->storage as $item) {
            if ( $item->getStatus() == Task::STATUS_QUEUED ) {
                return $item;
            }
        }

        return null;
    }

    public function update(Task $task): Task
    {
        $this->storage[$task->getId()] = $task;
        return $task;
    }

    public function push(Task $task): Task
    {
        $task->setId(count($this->storage));
        $this->storage[] = $task;
        return $task;
    }
}
