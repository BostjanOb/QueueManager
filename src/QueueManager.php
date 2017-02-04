<?php

namespace BostjanOb\QueuePlatform;

use BostjanOb\QueuePlatform\Rpc\Server;
use BostjanOb\QueuePlatform\Storage\Storage;

/**
 * Class QueueManager
 * @author Bostjan Oblak
 * @package BostjanOb\QueuePlatform
 */
class QueueManager
{
    /**
     * @var array
     */
    private $workers = [];
    /**
     * @var Storage
     */
    private $storage;

    /**
     * QueueManager constructor.
     * @param Storage $storage
     */
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
        if ( ! preg_match('/^[\w-]+$/', $name) ) {
            throw new \InvalidArgumentException('Worker name could contains only word characters and -_!');
        }

        $this->workers[$name] = $worker;
        return $this;
    }

    /**
     * Queue new job
     * @param string $name
     * @param array $params
     * @return Task
     */
    public function queueTask(string $name, $params = null): Task
    {
        if ( ! isset($this->workers[$name]) ) {
            throw new \InvalidArgumentException('Worker does not exists');
        }

        $task = Task::createNew($name, $params);
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

        $task->setCompleted($result);
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
     * todo
     */
    public function work()
    {
        global $argv;

        if ( !isset($argv[1]) || false === filter_var($argv[ count($argv)-1 ], FILTER_VALIDATE_URL) ) {
            $this->cliHelp();
            exit;
        }

        $opt = getopt('', ['workers::', 'sleep::']);

        $workers = $this->workers;
        if ( isset($opt['workers']) ) {
            $optWorkers = explode(',', $opt['workers']);
            if ( array_diff( $optWorkers, array_keys($this->workers)) ) {
                die('Invalid worker');
            }

            $workers = array_intersect_key( $this->workers, array_flip($optWorkers) );
        }

        $process = new Process($argv[ count($argv)-1 ], $workers);

        if ( isset($opt['sleep']) ) {
            $process->setSleep((int)$opt['sleep']);
        }

        $process->run();
    }

    private function cliHelp() {
        echo "To run worker process: php file [options] MANAGER_URL
            Options:
                --workers : list witch workers to run (default to all), example: --workers=foo,bar
                --sleep : how many seconds to sleep if there is no job, example: --sleep=3
                ";
    }
}