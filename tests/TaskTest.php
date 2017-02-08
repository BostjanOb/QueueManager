<?php

use BostjanOb\QueuePlatform\Task;

class TaskTest extends \PHPUnit\Framework\TestCase
{

    public function testDefaultStatusQueued()
    {
        $task = new Task();
        $this->assertEquals(Task::STATUS_QUEUED, $task->getStatus());
    }

    public function testCreateNewCreatesCorrectTask()
    {
        $task = Task::createNew('foo', 'bar');
        $this->assertEquals(Task::STATUS_QUEUED, $task->getStatus());
        $this->assertEquals('foo', $task->getName());
        $this->assertEquals('bar', $task->getParams());
    }

    public function testIsCompletedReturnsFalseForNewTask()
    {
        $task = new Task();
        $this->assertFalse($task->isCompleted());
    }

    public function testSetCompletedSetsCorrectData()
    {
        $task = new Task();

        $task->setCompleted('result');
        $this->assertTrue($task->isCompleted());
        $this->assertEquals('result', $task->getResult());
        $this->assertNotNull($task->getCompletedAt());
    }

    public function testSetSetFailedSetsCorrectData()
    {
        $task = new Task();

        $task->setFailed('failed');
        $this->assertTrue($task->isFailed());
        $this->assertEquals('failed', $task->getResult());
        $this->assertNotNull($task->getCompletedAt());
    }

    public function testStartWorkingSetsCorrectStatus()
    {
        $task = new Task();

        $task->startWorking();
        $this->assertEquals(Task::STATUS_RUNNING, $task->getStatus());
        $this->assertNotNull($task->getStartedAt());
        $this->assertNull($task->getCompletedAt());
    }

    public function testToArrayReturnsFullArray()
    {
        $task = new Task();
        $task->setCompleted('result');

        $result = [
            'id'           => null,
            'name'         => null,
            'params'       => null,
            'result'       => "result",
            'started_at'   => null,
            'completed_at' => $task->getCompletedAt(),
            'status'       => "COMPLETED",
        ];
        $this->assertEquals($result, $task->toArray());
    }

    public function testJsonEncodeReturnsCorrectData()
    {
        $task = new Task();
        $task->setCompleted('result');

        $result = [
            'id'           => null,
            'name'         => null,
            'params'       => null,
            'result'       => "result",
            'started_at'   => null,
            'completed_at' => $task->getCompletedAt(),
            'status'       => "COMPLETED",
        ];
        $this->assertEquals( json_encode($result), json_encode($task) );
    }

}