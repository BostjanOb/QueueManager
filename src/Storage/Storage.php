<?php

namespace BostjanOb\QueuePlatform\Storage;

use BostjanOb\QueuePlatform\Task;

/**
 * Interface Storage
 * @package BostjanOb\QueuePlatform\Storage
 */
interface Storage
{

    /**
     * Get task from queue
     *
     * @param int $id
     * @return Task|null
     */
    public function get(int $id): ?Task;

    /**
     * Update task in queue
     *
     * @param Task $task
     * @return Task
     */
    public function update(Task $task): Task;

    /**
     * Get task to work on and mark it as working
     *
     * @param array|null $workers
     * @return Task|null
     */
    public function pop(?array $workers = []): ?Task;

    /**
     * Push new task to queue
     *
     * @param Task $task
     * @return Task
     */
    public function push(Task $task): Task;
}