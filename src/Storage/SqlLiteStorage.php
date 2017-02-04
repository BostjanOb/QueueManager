<?php

namespace BostjanOb\QueuePlatform\Storage;

use BostjanOb\QueuePlatform\Task;

class SqlLiteStorage implements Storage
{
    protected $db;

    public function __construct(string $path)
    {
        $this->db = new \PDO('sqlite:' . $path);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function get(int $id): ?Task
    {
        $stmt = $this->db->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$taskData) {
            return null;
        }

        return new Task($taskData);
    }

    public function getQueued(?array $workers = []): ?Task
    {
        $sql = "SELECT * FROM tasks WHERE status = :status";
        if ( count($workers) ) {
            array_walk($workers, function(&$item) { $item = $this->db->quote($item); });
            $sql .= " AND name IN (" . implode(',', $workers) . ")";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => Task::STATUS_QUEUED]);

        $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$taskData) {
            return null;
        }

        return new Task($taskData);
    }

    public function update(Task $task): Task
    {
        $stmt = $this->db->prepare("UPDATE tasks SET 
                status = :status, 
                result = :result, 
                started_at = :started_at,
                completed_at = :completed_at
                WHERE id = :id");

        $stmt->execute([
            'status' => $task->status,
            'result' => $task->result,
            'started_at' => $task->started_at,
            'completed_at' => $task->completed_at,
            'id' => $task->id
        ]);

        return $task;
    }

    public function add(Task $task): Task
    {
        $stmt = $this->db->prepare("INSERT INTO tasks (name, params, status) VALUES (:name, :params, :status)");
        $stmt->execute([
            ':name' => $task->name,
            'params' => json_encode($task->params),
            'status' => Task::STATUS_QUEUED
        ]);
        $task->id = $this->db->lastInsertId();

        return $task;
    }

    public function createTable()
    {
        $sql = "BEGIN;
            CREATE TABLE IF NOT EXISTS tasks (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                name          VARCHAR NOT NULL,
                params        TEXT    NULL,
                result        TEXT    NULL,
                started_at    INT     NULL,
                completed_at  INT     NULL,
                status        VARCHAR NOT NULL DEFAULT 'QUEUED'
            );
            CREATE INDEX queued_tasks_IX ON tasks (name, status);
        COMMIT;";

        $this->db->exec($sql);
    }
}