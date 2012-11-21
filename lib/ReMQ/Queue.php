<?php

namespace ReMQ;

class Queue extends ReMQ
{

    /**
     * Queue name.
     * @var string Queue name in Redis.
     */
    private $queue;

    /**
     * Create a new queue.
     *
     * @param string $queue Queue name
     */
    public function __construct($queue)
    {
        $this->queue = 'remq:' . strtolower($queue);
    }

    /**
     * Queue up a job.
     *
     * @param object Job class
     * @param mixed Job parameters
     * @return boolean True on success, false on failure
     */
    public function enqueue()
    {
        $args = func_get_args();
        $class = $args[0];
        if ($this->isValidJob($class)) {
            $body = json_encode($args);
            return $this->redis()->rpush($this->queue, $body);
        }
    }

}
