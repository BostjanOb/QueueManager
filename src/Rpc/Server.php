<?php

namespace BostjanOb\QueuePlatform\Rpc;

/**
 * Simple JSON-RPC server
 * @author Bostjan Oblak
 */
use BostjanOb\QueuePlatform\Rpc\Exceptions\InvalidRequestException;
use BostjanOb\QueuePlatform\Rpc\Exceptions\MethodNotFoundException;
use BostjanOb\QueuePlatform\Rpc\Exceptions\ParseException;
use BostjanOb\QueuePlatform\Rpc\Exceptions\RpcException;

/**
 * Class Server
 * @package BostjanOb\QueuePlatform\Rpc
 */
class Server
{
    /**
     * @var string
     */
    private $request;

    /**
     * @var array
     */
    private $methods = [];

    /**
     * Server constructor.
     * @param string $request
     */
    public function __construct($request = '')
    {
        if ($request !== '') {
            $this->request = json_decode($request, true);
        } else {
            $this->request = json_decode(file_get_contents('php://input'), true);
        }
    }

    /**
     * Register object to run methods on
     * @param $object
     * @param array $methods Allowed methods
     */
    public function registerObject($object, array $methods = [])
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('First parameter must be object');
        }

        // no methods given ... get all public methods
        if (count($methods) == null) {
            // todo: switch to reflection
            $methods = get_class_methods($object);
        }

        foreach ($methods as $method) {
            $this->methods[$method] = $object;
        }
    }

    /**
     * Run server and return response
     * TODO: refactor
     */
    public function listen(): ?string
    {
        // todo: refactor this
//        header('Content-Type: application/json-rpc');
        try {
            $this->validateRequest();
        } catch (RpcException $e) {
            return json_encode([
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
                'id'      => $e->getId(),
            ]);
        }

        // valid request ... run it
        try {
            $params = $this->request['params'] ?? [];
            $result = call_user_func_array([$this->methods[$this->request['method']], $this->request['method']],
                $params);
            if (!isset($this->request['id'])) {
                // is notify so no result is requested
                return null;
            }
        }
        catch (\Exception $e) {
            return json_encode([
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32000,
                    'message' => $e->getMessage(),
                ],
                'id' => $this->request['id'] ?? null
            ]);
        }

        return json_encode([
            'jsonrpc' => '2.0',
            'result' => $result,
            'id' => $this->request['id']
        ]);
    }

    /**
     * Validate request payload (json)
     * @throws InvalidRequestException
     * @throws MethodNotFoundException
     * @throws ParseException
     */
    private function validateRequest(): void
    {
        // parsing json failed
        if (null == $this->request) {
            throw new ParseException();
        }

        // json is invalid
        if (!isset($this->request['jsonrpc']) ||
            !isset($this->request['method']) ||
            !is_string($this->request['method']) ||
            $this->request['jsonrpc'] !== '2.0' ||
            (isset($this->request['params']) && !is_array($this->request['params']))
        ) {
            $invalidException = new InvalidRequestException();
            $invalidException->setId($this->request['id'] ?? null);
            throw $invalidException;
        }

        // method does not exits
        if (!isset($this->methods[$this->request['method']])) {
            $methodNotFound = new MethodNotFoundException();
            $methodNotFound->setId($this->request['id'] ?? null);
            throw $methodNotFound;
        }
    }
}