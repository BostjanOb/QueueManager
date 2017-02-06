<?php

use BostjanOb\QueuePlatform\Rpc\Server;

class DummyObject
{
    public function bar(): string
    {
        return 'Foo';
    }

    public function notify()
    {

    }

    public function subtract(int $param1, int $param2): int
    {
        return $param1 - $param2;
    }

    public function sum(int $param1, int $param2): int
    {
        return $param1 + $param2;
    }

    public function fail()
    {
        throw new \Exception('Fail');
    }
}

class ServerTest extends \PHPUnit\Framework\TestCase
{

    protected function setUp()
    {
        parent::setUp();

        Server::$headers = [];
    }


    public function testParseErrorForInvalidJson()
    {
        $req = '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]';
        $server = new Server($req);
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            $server->listen()
        );
    }

    public function testInvalidRequestObject()
    {
        $req = '{"jsonrpc": "2.0", "method": 1, "params": "bar"}';
        $server = new Server($req);
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            $server->listen()
        );
    }

    public function testInvalidMethodForNotRegisteredMethod()
    {
        $req = '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}';
        $server = new Server($req);
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}',
            $server->listen()
        );
    }

    public function testCallCorrectFunctionAndReturnResult()
    {
        $req = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';
        $server = new Server($req);
        $server->registerObject(new DummyObject());
        $this->assertEquals(
            '{"jsonrpc":"2.0","result":19,"id":1}',
            $server->listen()
        );

        $req = '{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 1}';
        $server = new Server($req);
        $server->registerObject(new DummyObject());
        $this->assertEquals(
            '{"jsonrpc":"2.0","result":-19,"id":1}',
            $server->listen()
        );
    }

    public function testMethodWithoutParams()
    {
        $req = '{"jsonrpc": "2.0", "method": "bar", "id": 1}';
        $server = new Server($req);
        $server->registerObject(new DummyObject());
        $this->assertEquals(
            '{"jsonrpc":"2.0","result":"Foo","id":1}',
            $server->listen()
        );
    }

    public function testRegisterOnlyGivenMethods()
    {
        $req = '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}';
        $server = new Server($req);
        $server->registerObject(new DummyObject(), ['bar']);
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}',
            $server->listen()
        );

        $req = '{"jsonrpc": "2.0", "method": "bar", "id": 1}';
        $server = new Server($req);
        $server->registerObject(new DummyObject(), ['bar']);
        $this->assertEquals(
            '{"jsonrpc":"2.0","result":"Foo","id":1}',
            $server->listen()
        );
    }

    public function testHandleException()
    {
        $req = '{"jsonrpc": "2.0", "method": "fail", "params": [], "id": 1}';
        $server = new Server($req);
        $server->registerObject(new DummyObject());
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32000,"message":"Fail"},"id":1}',
            $server->listen()
        );
    }

    public function testCallWithEmptyArray()
    {
        $req = '[]';
        $server = new Server($req);
        $server->registerObject(new DummyObject());
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            $server->listen()
        );
    }

    public function testCallWithNotEmptyInvalidBatch()
    {
        $req = '[1]';
        $server = new Server($req);
        $server->registerObject(new DummyObject());
        $this->assertEquals(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            $server->listen()
        );
    }

    public function testCallWithInvalidBatch()
    {
        $req = '[1,2,3]';
        $server = new Server($req);
        $server->registerObject(new DummyObject());

        $result = json_decode($server->listen(), true);

        $this->assertEquals(3, count($result));
        $this->assertEquals([
                ["jsonrpc" => "2.0", "error" => ["code" => -32600, "message" => "Invalid Request"], "id" => null],
                ["jsonrpc" => "2.0", "error" => ["code" => -32600, "message" => "Invalid Request"], "id" => null],
                ["jsonrpc" => "2.0", "error" => ["code" => -32600, "message" => "Invalid Request"], "id" => null]
            ],
            $result
        );
    }

    public function testCallBatch()
    {
        $req = '[{"jsonrpc": "2.0", "method": "sum", "params": [2,4], "id": "1"},
        {"jsonrpc": "2.0", "method": "notify", "params": [7]},
        {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
        {"foo": "boo"},
        {"jsonrpc": "2.0", "method": "random-method", "params": [42,23], "id": "5"},
        {"jsonrpc": "2.0", "method": "bar", "id": "9"}]';

        $server = new Server($req);
        $server->registerObject(new DummyObject());

        $result = json_decode($server->listen(), true);

        $this->assertEquals(5, count($result));

        $this->assertContains(["jsonrpc" => "2.0", "result" => 6, "id" => "1"], $result);
        $this->assertContains(["jsonrpc" => "2.0", "result" => 19, "id" => "2"], $result);
        $this->assertContains(["jsonrpc" => "2.0", "result" => "Foo", "id" => "9"], $result);

        $this->assertContains(["jsonrpc" => "2.0", "error" => ["code" => -32600, "message" => "Invalid Request"], "id" => null], $result);
        $this->assertContains(["jsonrpc" => "2.0", "error" => ["code" => -32601, "message" => "Method not found"], "id" => "5"], $result);
    }

}