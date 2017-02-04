<?php

namespace BostjanOb\QueuePlatform;

class Task implements \JsonSerializable
{
    const STATUS_QUEUED = 'QUEUED';
    const STATUS_RUNNING = 'RUNNING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_FAILED = 'FAILED';

    private $data = [];

    /**
     * Task constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }


    public function isCompleted() {
        return $this->status == self::STATUS_COMPLETED;
    }

    /**
     * @param string $name
     * @return mixed
     */
    function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    function __set($name, $value)
    {
        $this->data[$name] = $value;
    }


    function jsonSerialize()
    {
        return json_encode($this->data);
        // TODO: Implement jsonSerialize() method.
    }
}