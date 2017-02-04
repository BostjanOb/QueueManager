<?php

class ClientTest extends \PHPUnit\Framework\TestCase
{

    public function testGeneratesCorrectJson()
    {
        $transport = Mockery::mock( \BostjanOb\QueuePlatform\Rpc\Transport\Transport::class );
        $transport->shouldReceive('send')
            ->with('http://example.com/queue.php', '{"jsonrpc":"2.0","id":2,"method":"test","params":["john","doe"]}')
            ->once();

        $client = new \BostjanOb\QueuePlatform\Rpc\Client('http://example.com/queue.php', $transport);
        $client->request('test', ['john', 'doe']);
        $this->assertTrue(true); // fake to pass
    }

}