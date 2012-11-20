<?php

namespace ReMQ;

class BadJobException extends \Exception { }

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
     * Check if the job is valid (has process method).
     *
     * @param string $class Class name
     * @throws BadJobException if the job isn't valid
     * @return boolean True if job is valid
     */
    private function isValidJob($class)
    {
        if (!method_exists($class, 'process')) {
            throw new BadJobException($class . ' is not a valid job');
            return false;
        }
        return true;
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
