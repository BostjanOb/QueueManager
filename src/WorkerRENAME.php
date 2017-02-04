<?php

namespace BostjanOb\QueuePlatform;

class WorkerRENAME
{
    private $managerUri;

    private $sleep = 3;

    private $jobs = [];

    public function __construct(array $jobs)
    {
        // parse cli params
    }

    public function run() {

        while (true) {
            $job = $this->getNewJob();
            if ($job == null) {
                sleep($this->sleep);
                continue;
            }



            // check for task

        }

    }

    private function getNewJob()
    {
    }
}