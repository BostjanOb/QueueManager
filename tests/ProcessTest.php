<?php

use BostjanOb\QueuePlatform\Process;
use BostjanOb\QueuePlatform\Worker;

use \Mockery as m;

class ProcessTest extends \PHPUnit\Framework\TestCase
{

    public function testSendCorrectResult()
    {
        $task = ["result" => ["id" => 1, "name" => "square", "params" => 3]];
        $client = m::mock(\BostjanOb\QueuePlatform\Rpc\Client::class);

        $client->shouldReceive('request')->with('getQueuedTask')->once()->andReturn($task);
        $client->shouldReceive('request')->with('completeTask', [1, 9])->once();

        $processor = new Process($client, ['square' => new SquareWorker()], 1);
        $processor->run();

        $this->assertTrue(true); // remove warning
    }

    public function testLimitEndsRunning()
    {
        $task = ["result" => ["id" => 1, "name" => "square", "params" => 3]];
        $client = m::mock(\BostjanOb\QueuePlatform\Rpc\Client::class);

        $client->shouldReceive('request')->with('getQueuedTask')->twice()->andReturn($task);
        $client->shouldReceive('request')->with('completeTask', [1, 9])->twice();

        $processor = new Process($client, ['square' => new SquareWorker()], 2);
        $processor->run();

        $this->assertTrue(true); // remove warning
    }

    public function testLogInvalidMethod()
    {
        $task = ["result" => ["id" => 1, "name" => "unknown_method", "params" => 3]];
        $client = m::mock(\BostjanOb\QueuePlatform\Rpc\Client::class);

        $client->shouldReceive('request')->with('getQueuedTask')->once()->andReturn($task);
        $client->shouldReceive('request')->with('failedTask', [1, ["exception" => "Exception", "message" => "Invalid task", "code" => 0]])->once();

        $processor = new Process($client, ['square' => new SquareWorker()], 1);
        $processor->run();

        $this->assertTrue(true); // remove warning
    }

    public function testTaskException()
    {
        $task = ["result" => ["id" => 1, "name" => "square", "params" => 'invalid param']];
        $client = m::mock(\BostjanOb\QueuePlatform\Rpc\Client::class);

        $client->shouldReceive('request')->with('getQueuedTask')->once()->andReturn($task);
        $client->shouldReceive('request')->with('completeTask', [1, 9])->never();
        $client->shouldReceive('request')->with('failedTask', [1, ["exception" => "InvalidArgumentException", "message" => "Param should be numeric value", "code" => 0]])->once();

        $processor = new Process($client, ['square' => new SquareWorker()], 1);
        $processor->run();

        $this->assertTrue(true); // remove warning
    }

    public function testExceptionIsReceivedAsTask()
    {
        $return = [
            "jsonrpc" => "2.0",
            "error"   => [
                "code"    => -32000,
                "message" => "SQLSTATE[HY000] [14] unable to open database file"
            ],
            "id"      => 3
        ];
        $task = ["result" => ["id" => 1, "name" => "square", "params" => 3]];

        $client = m::mock(\BostjanOb\QueuePlatform\Rpc\Client::class);

        $client->shouldReceive('request')->with('getQueuedTask')->once()->andReturn($return);
        $client->shouldReceive('request')->with('getQueuedTask')->once()->andReturn($task);
        $client->shouldReceive('request')->with('completeTask', [1, 9])->once();

        $processor = new Process($client, ['square' => new SquareWorker()], 1);
        $processor->setSleep(0);
        $processor->run();

        $this->assertTrue(true); // remove warning
    }

    public function tearDown()
    {
        m::close();
    }
}

class SquareWorker implements Worker
{
    /**
     * @param null $params
     * @return mixed
     */
    public function run($params = null)
    {
        if (!is_numeric($params)) {
            throw new \InvalidArgumentException('Param should be numeric value');
        }

        return $params * $params;
    }
}
