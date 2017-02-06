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
        $task = $this->storage->push(Task::createNew('foo', 123));
        $this->assertEquals(1, $task->getId());
        $this->assertEquals(123, $task->getParams());
    }

    public function testTaskGetsUpdated()
    {
        $task = $this->storage->push(Task::createNew('foo', 123));

        $task->setResult(3);
        $task->setStatus(Task::STATUS_COMPLETED);

        $this->storage->update($task);

        $dbTask = $this->storage->get($task->getId());

        $this->assertTrue($dbTask->isCompleted());
        $this->assertEquals(3, $dbTask->getResult());
    }

    public function testGetReturnsTask()
    {
        $task = $this->storage->push(Task::createNew('foo', 123));

        $dbTask = $this->storage->get($task->getId());
        $this->assertEquals('foo', $dbTask->getName());
        $this->assertFalse($dbTask->isCompleted());
    }

    public function testGetQueuedJob()
    {
        $this->storage->push(Task::createNew('foo', 123));
        $this->storage->push(Task::createNew('bar', 321));
        $this->storage->push(Task::createNew('john', 'doe'));

        $task = $this->storage->pop();

        $this->assertEquals('foo', $task->getName());
    }

    public function testGetQueuedJobForSingleWorker()
    {
        $this->storage->push(Task::createNew('foo', 123));
        $this->storage->push(Task::createNew('bar', 321));
        $this->storage->push(Task::createNew('john', 'doe'));

        $task = $this->storage->pop(['john']);
        $this->assertEquals('john', $task->getName());
    }

    public function testGetQueuedJobForMultipleWorkers()
    {
        $this->storage->push(Task::createNew('foo', 123));
        $this->storage->push(Task::createNew('bar', 321));
        $this->storage->push(Task::createNew('john', 'doe'));

        $task = $this->storage->pop(['john', 'bar']);
        $this->assertEquals('bar', $task->getName());
    }

    public function testReturnNullIfNoTaskExists()
    {
        $task = $this->storage->pop(['john', 'bar']);
        $this->assertNull($task);
    }

    public function testDontGetSameJobs()
    {
        $this->storage->push(Task::createNew('bar', 321));
        $this->storage->push(Task::createNew('foo', 321));

        $task1 = $this->storage->pop();
        $task2 = $this->storage->pop();

        $this->assertEquals('bar', $task1->getName());
        $this->assertEquals('foo', $task2->getName());
    }

    public function testMarkTaskAsRunnig()
    {
        $this->storage->push(Task::createNew('bar', 321));
        $task = $this->storage->pop();

        $this->assertEquals(Task::STATUS_RUNNING, $task->getStatus());
    }

    public function testReturnNullIfAllTasksAreCompleted()
    {
        $t1 = $this->storage->push(Task::createNew('foo', 123));
        $t2 = $this->storage->push(Task::createNew('bar', 321));

        $t1->setStatus(Task::STATUS_COMPLETED);
        $t2->setStatus(Task::STATUS_COMPLETED);

        $this->storage->update($t1);
        $this->storage->update($t2);

        $task = $this->storage->pop(['john', 'bar']);
        $this->assertNull($task);
    }
}
