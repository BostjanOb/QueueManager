<?php

namespace BostjanOb\QueuePlatform;

use BostjanOb\QueuePlatform\Rpc\Client;
use BostjanOb\QueuePlatform\Rpc\Transport\FileGetContentsTransport;

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
     * Process constructor.
     * @param string $managerUri
     * @param array $workers
     */
    public function __construct(string $managerUri, array $workers)
    {
        $this->client = new Client($managerUri, new FileGetContentsTransport());
        $this->workers = $workers;
    }

    /**
     * Run process
     */
    public function run()
    {
        while (true) {
            $task = $this->getNewTask();
            if ($task == null) {
                sleep($this->sleep);
                continue;
            }

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
                // todo: send failed response
            }
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
        }
        catch (\Exception $e) {
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
        }
        catch (\Exception $e) {}
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