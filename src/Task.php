<?php

namespace BostjanOb\QueuePlatform;

/**
 * Class Task
 * @package BostjanOb\QueuePlatform
 */
class Task implements \JsonSerializable
{
    const STATUS_QUEUED = 'QUEUED';
    const STATUS_RUNNING = 'RUNNING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_FAILED = 'FAILED';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $params;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var int
     */
    private $started_at;

    /**
     * @var int
     */
    private $completed_at;

    /**
     * @var string
     */
    private $status;

    /**
     * Task constructor.
     */
    public function __construct()
    {
        $this->setStatus(self::STATUS_QUEUED);
    }

    /**
     * Creates new task
     *
     * @param $name
     * @param $params
     * @return Task
     */
    public static function createNew($name, $params): Task
    {
        $task = new self();
        $task->setName($name);
        $task->setParams($params);

        return $task;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    /**
     * @param $result
     */
    public function setCompleted($result) {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = time();
        $this->result = $result;
    }

    /**
     *
     */
    public function startWorking()
    {
        $this->status = self::STATUS_RUNNING;
        $this->started_at = time();
    }

    /**
     * @return array
     */
    function toArray()
    {
        return [
            'id'           => $this->getId(),
            'name'         => $this->getName(),
            'params'       => $this->getParams(),
            'result'       => $this->getResult(),
            'started_at'   => $this->getStartedAt(),
            'completed_at' => $this->getCompletedAt(),
            'status'       => $this->getStatus(),
        ];
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Task
     */
    public function setId(int $id): Task
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Task
     */
    public function setName(string $name): Task
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param mixed $params
     * @return Task
     */
    public function setParams($params): Task
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartedAt(): ?int
    {
        return $this->started_at;
    }

    /**
     * @param int $started_at
     * @return Task
     */
    public function setStartedAt(?int $started_at): Task
    {
        $this->started_at = $started_at;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompletedAt(): ?int
    {
        return $this->completed_at;
    }

    /**
     * @param int $completed_at
     * @return Task
     */
    public function setCompletedAt(?int $completed_at): Task
    {
        $this->completed_at = $completed_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Task
     */
    public function setStatus(string $status): Task
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return Task
     */
    public function setResult($result): Task
    {
        $this->result = $result;
        return $this;
    }

}