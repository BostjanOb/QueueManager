# Queue Platform

[![Build Status](https://travis-ci.org/BostjanOb/QueuePlatform.svg?branch=master)](https://travis-ci.org/BostjanOb/QueuePlatform)

PHP Queue Platform provides an easy way to build queue system in PHP.

## Easy way

The easiest path to use queue platform is to use [QueuePlatformExample repository](https://github.com/BostjanOb/QueuePlatformExample).

QueuePlatformExample provides a complete working example with sample workers.
To run and test it, it comes with configured vagrant setup. To use it just boot up vagrant with `vagrant up`.

Vagrant setup will set up a complete environment. It will start 5 working processes, setup queue manager URL and server testing GUI.

URL to testing GUI: [http://192.168.29.6/index.html](http://192.168.29.6/index.html)

How to modify it, see docs in QueuePlatformExample repository.

## Hard way

### 1. require package
Require QueuePlatform package with composer: `composer require bostjanob/queue-platform`.

### 2. create workers
Workers must extend `BostjanOb\QueuePlatform\Worker` interface.

The only method to implement is `run($params = null)`.

### 3. create QueueManager class and register workers

Create a new `BostjanOb\QueuePlatform\QueueManager` object and register your workers with it.

For constructor, you **must** provide storage object (object that implements `\BostjanOb\QueuePlatform\Storage\Storage`)

```php
<?php

// queuemanager.php
$storage = new \BostjanOb\QueuePlatform\Storage\SqlLiteStorage('db.sqlite3');
$qm = new \BostjanOb\QueuePlatform\QueueManager($storage);

$qm->registerWorker('task-name', new Worker());
```

### 4. make queue manager JSON-RPC public

To push task, get task status and to make communication with working processes QueueManager uses JSON-RPC. So it must be accessible by a web server (over URL).

To listen for json-rpc request run `listen()` on QueueManager.

```php
<?php

// queue.php - server over web server
require 'queuemanager.php';
echo $qm->listen();
```

### 5. start working processes

To start long running working process call `work()` on QueueManager. Run it from CLI and pass URL to QueueManager (in step 4) as a parameter.

```php
<?php

// process.php
require 'queuemanager.php';
$qm->work();
```
Run from CLI:
```bash
php process.php http://example.com/queue.php
```

## QueueManager JSON-RPC SERVER

QueueManager JSON-RPC provides two methods to interact with.

### - queueTask

Queue task to be then later pulled by the processor. It accepts one or two parameters.

**the first parameter** is the name of a worker registered with a queue manager

**the second parameter** is a parameter for the worker. It accepts a single value. If you want to pass multiple values use an array.

**returned result** is a task object as a JSON-RPC result

### - getTask

getTask method return task object. The method requires id of a task as a first parameter.

## QueueManager CLI working process

To run long running process run PHP script from CLI (step 5.).

Command to start process:
```bash
php file.php [OPTIONS] URL_TO_QUEUEMANAGER
```

##### Options
Available options:
  - **workers** - List workers to work with. Default to all registered in QueueManage. (split by comma)
  - **sleep** - How many seconds to wait if there is no task. The process will check if there is some task and if there is none it will wait before checking again.

Example with options:
```bash
php file.php --workers=foo,bar --sleep=3 http://example.com/queue.php
```
