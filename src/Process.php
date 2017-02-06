<?php

namespace BostjanOb\QueuePlatform;

use BostjanOb\QueuePlatform\Rpc\Client;

/**
 * Class Process
 * @package BostjanOb\QueuePlatform
 */
class Process
{
    /**
     * Sleep interval where there are no jobs
     * @var int
     */
    private $sleep = 3;

    /**
     * Available workers
     * @var array
     */
    private $workers;

    /**
     * RPC client
     * @var Client
     */
    private $client;
    /**
     * @var bool
     */
    private $limitTask;

    /**
     * Process constructor.
     * @param Client $rpcClient
     * @param array $workers
     * @param bool $limitTask
     * @internal param string $managerUri
     */
    public function __construct(Client $rpcClient, array $workers, $limitTask = false)
    {
        $this->client = $rpcClient;
        $this->workers = $workers;
        $this->limitTask = $limitTask;
    }

    /**
     * Run process
     */
    public function run()
    {
        while (true) {
            if (!$this->limitTask && $this->limitTask === 0) {
                break;
            }

            $task = $this->getNewTask();
            if ($task == null) {
                sleep($this->sleep);
                continue;
            }

            $this->runTask($task);

            if ($this->limitTask) {
                $this->limitTask--;
            }
        }
    }

    /**
     * @param $task
     */
    protected function runTask($task): void
    {
        try {
            if (!isset($this->workers[$task['name']])) {
                throw new \Exception('Invalid task');
            }

            $result = call_user_func_array(
                [$this->workers[$task['name']], 'run'],
                [$task['params']]
            );

            $this->sendResult($task['id'], $result);
        } catch (\Exception $e) {
            $this->sendException($task['id'], $e);
        }
    }

    /**
     * Get new task from queue manager
     * @return null
     */
    protected function getNewTask()
    {
        try {
            $task = $this->client->request('getQueuedTask');
        } catch (\Exception $e) {
            return null;
        }
        return $task['result'];
    }

    /**
     * Send completed result to queue manager
     * @param $id
     * @param $result
     */
    protected function sendResult($id, $result)
    {
        try {
            $this->client->request('completeTask', [$id, $result]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Send failed result back to queue manager
     * @param $id
     * @param \Exception $e
     */
    protected function sendException($id, \Exception $e)
    {
        $exception = [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
            'code'      => $e->getCode(),
        ];

        try {
            $this->client->request('failedTask', [$id, $exception]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Set sleep interval
     * @param int $sleep
     */
    public function setSleep(int $sleep)
    {
        $this->sleep = $sleep;
    }
}
