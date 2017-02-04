<?php

namespace BostjanOb\QueuePlatform;

use phpDocumentor\Reflection\Types\Self_;

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

    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    public function setCompleted($result) {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = time();
        $this->result = $result;
    }

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
     */
    public function setId(int $id)
    {
        $this->id = $id;
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
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
     */
    public function setParams($params)
    {
        $this->params = $params;
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
     */
    public function setStartedAt(?int $started_at)
    {
        $this->started_at = $started_at;
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
     */
    public function setCompletedAt(?int $completed_at)
    {
        $this->completed_at = $completed_at;
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
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
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
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

}