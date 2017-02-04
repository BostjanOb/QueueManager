<?php

namespace BostjanOb\QueuePlatform;

use BostjanOb\QueuePlatform\Rpc\Server;
use BostjanOb\QueuePlatform\Storage\Storage;

class QueueManager
{
    private $workers = [];
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Register new job in queue manager
     * @param string $name
     * @param Worker $worker
     * @return QueueManager
     * @internal param Job $job
     */
    public function registerWorker(string $name, Worker $worker): QueueManager
    {
        $this->workers[$name] = $worker;
        return $this;
    }

    /**
     * Queue new job
     * @param string $name
     * @param array $params
     * @return Task
     */
    public function queueTask(string $name, array $params = []): Task
    {
        if ( ! isset($this->workers[$name]) ) {
            throw new \InvalidArgumentException();
        }

        $task = new Task([
            'name' => $name,
            'params' => $params
        ]);

        return $this->storage->add($task);
    }

    /**
     * Get job by id, for status and result checking
     * @param int $id
     * @return Task|null
     */
    public function getTask(int $id): ?Task
    {
        return $this->storage->get($id);
    }

    /**
     * Get next available job for worker
     * @param array|null $workers
     * @return Task|null
     * @internal param array|null $jobs Accepted jobs
     */
    public function getQueuedTask(?array $workers = null): ?Task
    {
        return $this->storage->getQueued($workers);
    }


    /**
     * Get status from worker of completed job
     * @param int $id
     * @param $result
     */
    public function completeTask(int $id, $result): void
    {
        $task = $this->getTask($id);

        $task->status = Task::STATUS_COMPLETED;
        $task->completed_at = new \DateTime();
        $task->result = $result;

        $this->storage->update($task);
    }

    /**
     * Run RPC Server
     * @return null|string
     */
    public function listen(): ?string
    {
        $rpcServer = new Server();
        $rpcServer->registerObject($this, ['queueTask', 'getTask', 'getQueuedTask', 'completeTask']);
        return $rpcServer->listen();
    }

    /**
     * Start queue work
     */
    public function work()
    {
        // parse cli

        // start loop
            // get task
            // execute task
            // send result
    }
}