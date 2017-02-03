# Just thinking out loud about this project (making some plans)

Queue manager should be simple to start and maintain. Jobs should be easy to implement and run (preferred  with supervisior).

## How to use it

### Basic operations
Basic operations should be done through QueueManager main object. QueueManager will run as json-rpc server (over http - could run over nginx, apache, build in server).

Operations:

```php
<?php
$qm = new QueueManager();

// basic operations to work with queue
$qm->queue($taskName, $parameters); // return Job object
$qm->getJob($id); // return Job object

// operations to work with worker
$qm->getNextJob($jobs = null) // get next job to be run
$qm->completeJob($job_id, $result) // complete job and set result
```

### CLI processes - workers

User should  be able to start as many workers as he wish.....anywhere. Workers run indefinitely and they query QueueManager to check if there is task. QueueManager decide wich job to give to worker. Worker can accept all job or can specify which jobs is processing.

When worker completes job it send result back to manager with `job_id` and `result`

## Classes

### QueueManager
Main class that handles big things. It must be able to queue task, send tasks to workers, process results from workers.

It should know of storage container (how data is stored - sqlite / mysql / redis / ...).

### Worker
Class that represents worker....worker is contacting manager to get some jobs and then execute this job.

### Job

Interface that represents job to do....should be implemented by user.

It should implement methods:

 - method to execute task - *run / execute / work / handle*
