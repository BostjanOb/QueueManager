<?php

use BostjanOb\QueuePlatform\Rpc\Server;

class DummyObject
{
    public function bar(): string
    {
        return 'Foo';
    }

    public function subtract(int $param1, int $param2): int
    {
        return $param1 - $param2;
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
}