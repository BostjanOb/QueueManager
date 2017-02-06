<?php

namespace BostjanOb\QueuePlatform\Storage;

/**
 * Class SqlLiteStorage
 * @package BostjanOb\QueuePlatform\Storage
 */
class SqlLiteStorage extends PdoStorage
{
    /**
     * SqlLiteStorage constructor.
     * @param string $dsn Path to te sqlite file
     */
    public function __construct(string $dsn)
    {
        parent::__construct('sqlite:' . $dsn);
    }

    /**
     * Create sqlite table
     */
    public function createTable()
    {
        $this->connect();

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
