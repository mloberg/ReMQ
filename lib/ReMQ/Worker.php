<?php

namespace ReMQ;

class Worker extends ReMQ
{

    /**
     * List of queues to process.
     * @var array Queses
     */
    private $queues = array();

    /**
     * Create a new ReMQ worker.
     *
     * @param string $queue optional name of queue
     */
    public function __construct($queue = null)
    {
        if ($queue) {
            $this->addQueue($queue);
        }
    }

    /**
     * Find queues in Redis.
     *
     * @param string $match Queue to find.
     */
    private function findQueues($match)
    {
        return $this->redis()->keys('remq:' . $match);
    }

    /**
     * Add a queue to worker.
     *
     * @param string $name Queue to add
     */
    public function addQueue($name)
    {
        array_push($this->queues, $name);
    }

    /**
     * Remove queues from the worker.
     *
     * @param string $name Queue to remove
     */
    public function removeQueue($name)
    {
        $this->queues = array_filter($this->queues, function($q) {
            return !$q === $name;
        });
    }

    /**
     * Return the list of queues for this worker.
     *
     * @return array Queues
     */
    public function queues()
    {
        return $this->queues;
    }

    /**
     * Run the worker.
     */
    public static function process()
    {
        //
    }

}
