<?php

namespace BostjanOb\QueuePlatform\Rpc;

use BostjanOb\QueuePlatform\Rpc\Exceptions\InvalidRequestException;
use BostjanOb\QueuePlatform\Rpc\Exceptions\MethodNotFoundException;
use BostjanOb\QueuePlatform\Rpc\Exceptions\ParseException;
use BostjanOb\QueuePlatform\Rpc\Exceptions\RpcException;

/**
 * Simple JSON-RPC server
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

    public static $headers = [
        'Content-Type: application/json-rpc',
    ];

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
        if (!count($methods)) {
            $methods = get_class_methods($object);
        }

        foreach ($methods as $method) {
            $this->methods[$method] = $object;
        }
    }

    /**
     * Run server and return response
     */
    public function listen(): ?string
    {
        if (count(self::$headers)) {
            header(implode("\r\n", self::$headers));
        }

        try {
            $this->validateRequest();

            $params = $this->request['params'] ?? [];
            $result = call_user_func_array([$this->methods[$this->request['method']], $this->request['method']],
                $params);

            if (!isset($this->request['id'])) {
                // is notify so no result is requested
                return null;
            }
        } catch (RpcException $e) {
            return $this->handleException($e->getCode(), $e->getMessage(), $e->getId());
        } catch (\Exception $e) {
            return $this->handleException(-32000, $e->getMessage(), $this->request['id'] ?? null);
        }

        return json_encode([
            'jsonrpc' => '2.0',
            'result'  => $result,
            'id'      => $this->request['id'],
        ]);
    }

    private function handleException(int $code, string $message, $id): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
            'id'      => $id,
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