<?php

namespace BostjanOb\QueuePlatform\Storage;

use BostjanOb\QueuePlatform\Task;

/**
 * Class PdoStorage
 * @package BostjanOb\QueuePlatform\Storage
 */
abstract class PdoStorage implements Storage
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var string
     */
    private $dsn;

    /**
     * PdoStorage constructor.
     * @param string $dsn
     */
    public function __construct(string $dsn)
    {
        $this->db = null;
        $this->dsn = $dsn;
    }

    protected function connect()
    {
        if (null != $this->db) {
            return;
        }

        $this->db = new \PDO($this->dsn);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * @param int $id
     * @return Task|null
     */
    public function get(int $id): ?Task
    {
        $this->connect();

        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $taskData = $stmt->fetch();

        if (!$taskData) {
            return null;
        }

        return $this->createTaskFromDb($taskData);
    }

    /**
     * @param Task $task
     * @return Task
     */
    public function update(Task $task): Task
    {
        $this->connect();

        $stmt = $this->db->prepare("UPDATE tasks SET 
                status = :status, 
                result = :result, 
                started_at = :started_at,
                completed_at = :completed_at
                WHERE id = :id");

        $stmt->execute([
            'status'       => $task->getStatus(),
            'result'       => json_encode(['data' => $task->getResult()]),
            'started_at'   => $task->getStartedAt(),
            'completed_at' => $task->getCompletedAt(),
            'id'           => $task->getId(),
        ]);

        return $task;
    }

    /**
     * @param array|null $workers
     * @return Task|null
     */
    public function pop(?array $workers = []): ?Task
    {
        $this->connect();

        $this->db->beginTransaction();
        $sql = "SELECT * FROM tasks WHERE status = :status";
        if (count($workers)) {
            $workers = array_map([$this->db, 'quote'], $workers);
            $sql .= " AND name IN (" . implode(',', $workers) . ")";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => Task::STATUS_QUEUED]);

        $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$taskData) {
            $this->db->commit();
            return null;
        }

        $task = $this->createTaskFromDb($taskData);
        $task->startWorking();
        $this->update($task);

        $this->db->commit();

        return $task;
    }

    /**
     * @param Task $task
     * @return Task
     */
    public function push(Task $task): Task
    {
        $this->connect();

        $stmt = $this->db->prepare("INSERT INTO tasks (name, params, status) VALUES (:name, :params, :status)");
        $stmt->execute([
            ':name'  => $task->getName(),
            'params' => json_encode(['data' => $task->getParams()]),
            'status' => $task->getStatus(),
        ]);

        $task->setId($this->lastInsertedId());

        return $task;
    }

    /**
     * @return string
     */
    protected function lastInsertedId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * @param $data
     * @return Task
     */
    protected function createTaskFromDb($data)
    {
        $task = new Task();
        $task->setId($data['id'])
            ->setName($data['name'])
            ->setStatus($data['status'])
            ->setStartedAt($data['started_at'])
            ->setCompletedAt($data['completed_at']);

        if (null != $data['params']) {
            $params = json_decode($data['params'], true);
            $task->setParams($params['data'] ?? null);
        }

        if (null != $data['result']) {
            $result = json_decode($data['result'], true);
            $task->setResult($result['data'] ?? null);
        }

        return $task;
    }
}
