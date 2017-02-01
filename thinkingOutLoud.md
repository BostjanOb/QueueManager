# Just thinking out loud about this project (making some plans)

Queue manager should be simple to start and maintain. Workers should be easy to implement and added queue manager (as separate process/server).

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
