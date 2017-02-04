<?php

use BostjanOb\QueuePlatform\Storage\SqlLiteStorage;
use BostjanOb\QueuePlatform\Task;

/**
 * Class SqlLiteStorageTest
 */
class SqlLiteStorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SqlLiteStorage
     */
    private $storage;

    protected function setUp()
    {
        parent::setUp();

        $this->storage = new SqlLiteStorage(':memory:');
        $this->storage->createTable();
    }


    public function testInsertSetId()
    {
        $task = $this->storage->add(new Task(['name' => 'foo', 'params' => 123]));
        $this->assertEquals(1, $task->id);
    }

    public function testTaskGetsUpdated()
    {
        $task = $this->storage->add(new Task(['name' => 'foo', 'params' => 123]));

        $task->result = 3;
        $task->status = Task::STATUS_COMPLETED;

        $this->storage->update($task);

        $dbTask = $this->storage->get($task->id);

        $this->assertTrue($dbTask->isCompleted());
    }

    public function testGetsReturnsTask()
    {
        $task = $this->storage->add(new Task(['name' => 'foo', 'params' => 123]));

        $dbTask = $this->storage->get($task->id);
        $this->assertEquals('foo', $dbTask->name);
        $this->assertFalse($dbTask->isCompleted());
    }

    public function testGetQueuedJob()
    {
        $this->storage->add(new Task(['name' => 'foo', 'params' => 123]));
        $this->storage->add(new Task(['name' => 'bar', 'params' => 123]));
        $this->storage->add(new Task(['name' => 'john', 'params' => 123]));

        $task = $this->storage->getQueued();

        $this->assertEquals('foo', $task->name);
    }

    public function testGetQueuedJobForSingleWorker()
    {
        $this->storage->add(new Task(['name' => 'foo', 'params' => 123]));
        $this->storage->add(new Task(['name' => 'bar', 'params' => 123]));
        $this->storage->add(new Task(['name' => 'john', 'params' => 123]));

        $task = $this->storage->getQueued(['john']);
        $this->assertEquals('john', $task->name);
    }

    public function testGetQueuedJobForMultipleWorkers()
    {
        $this->storage->add(new Task(['name' => 'foo', 'params' => 123]));
        $this->storage->add(new Task(['name' => 'bar', 'params' => 123]));
        $this->storage->add(new Task(['name' => 'john', 'params' => 123]));

        $task = $this->storage->getQueued(['john', 'bar']);
        $this->assertEquals('bar', $task->name);
    }

    public function testReturnNullIfNoTaskExists()
    {
        $task = $this->storage->getQueued(['john', 'bar']);
        $this->assertNull($task);
    }

    public function testReturnNullIfAllTasksAreCompleted()
    {
        $t1 = $this->storage->add(new Task(['name' => 'bar', 'params' => 123]));
        $t2 = $this->storage->add(new Task(['name' => 'john', 'params' => 123]));

        $t1->status = Task::STATUS_COMPLETED;
        $t2->status = Task::STATUS_COMPLETED;

        $this->storage->update($t1);
        $this->storage->update($t2);

        $task = $this->storage->getQueued(['john', 'bar']);
        $this->assertNull($task);
    }

}