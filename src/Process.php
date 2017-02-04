<?php

namespace BostjanOb\QueuePlatform;

use BostjanOb\QueuePlatform\Rpc\Client;
use BostjanOb\QueuePlatform\Rpc\Transport\FileGetContentsTransport;

class Process
{
    private $sleep = 3;

    private $workers;

    private $client;

    public function __construct(string $managerUri, array $workers)
    {
        $this->client = new Client($managerUri, new FileGetContentsTransport());
        $this->workers = $workers;
    }

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

    private function getNewTask()
    {
        try {
            $task = $this->client->request('getQueuedTask');
        }
        catch (\Exception $e) {
            return null;
        }
        return $task['result'];
    }

    public function sendResult($id, $result)
    {
        try {
            $this->client->request('completeTask', [$id, $result]);
        }
        catch (\Exception $e) {}
    }

    public function setSleep(int $sleep)
    {
        $this->sleep = $sleep;
    }
}