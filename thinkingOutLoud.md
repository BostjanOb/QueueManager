# Just thinking out loud about this project (making some plans)

Queue manager should be simple to start and maintain. Workers should be easy to implement and added queue manager (as separate process/server).

## How to use it

### Basic operations
Basic operations should be done through QueueManager main object.

Operations:

```php
<?php
$qm = new QueueManager();

$qm->queue($taskName, $parameters); // return Task object
$qm->getTask($id); // return Task object
```

### CLI processes
To use QueueManager platform user should use main QueueManager class, add some workers and run (cli) queuemanager and workers (same file? two files?)

Some idea how to run manager and workers:
```php
<?php

$storage = new SqLiteStorage('path');
$qm = new QueueManger($storage);

$qm->registerWorker('fibonacci', new Workers\Fibonacci());

$qm->run();
```

This could be run point for 2 different bin files (`./vendor/bin/qm-manager` and `./vendor/bin/qm-worker`). One to run manager and other to run/add workers.


How workers and manager communicate? Sockets? JSON-RPC? REST?

Check how to send request with curl without blocking (some sort async). If set timeout, check how to run script when connection is lost.

Some starting point:

```php
<?php

// manager sending task
$ch = curl_init($url_of_worker);
curl_setopt_array($ch, [
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER =>true,
    CURLOPT_NOSIGNAL => 1, //to timeout immediately if the value is < 1000 ms
    CURLOPT_TIMEOUT_MS => 50, //The maximum number of mseconds to allow cURL functions to execute
    CURLOPT_VERBOSE => 1,
    CURLOPT_HEADER => 1
]);
curl_exec($ch);
curl_close($ch);

// worker receiving task
// http://php.net/manual/en/function.ignore-user-abort.php
ignore_user_abort(true);
```


## Classes

### QueueManager
Main class that handles big things. It must be able to queue task, send tasks to workers, process results from workers.

It should know of storage container (how data is stored - sqlite / mysql / redis / ...).

### Worker
Interface/abstract class that represents worker....what to do.

It should implement methods:

 - method to execute task - *run / execute / work / handle*
 - method to process returned value, some sort of callback - *resultHandler / callback*

### Task
Plain class that only stores basic information about task.

Informations:

- id
- name - *task name, represents associated worker*
- parameters - *parameters for worker*
- result - *returned result from worker*
- status (queued, started, completed, failed)
- created_at
- updated_at
