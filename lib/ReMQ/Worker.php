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
        foreach ($this->queues as $key => $value) {
            if ($value === $name) {
                unset($this->queues[$key]);
            }
        }
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
        // need to match queues
        // trap CTRL-C
        pcntl_signal(SIGTERM, function($signo) {
            //
        });
        // loop
        if ($time === 0) {
            // loop forever
            $while = true;
        } else {
            $start = time();
            $while = ($start + $time) > time();
        }
        while ($while) {
            try {
                list($key, $value) = static::$redis->blpop($queue, 0);
                $body = json_decode($value);
                $class = array_shift($body);
                call_user_func_array(array($class, 'perform'), $body);
            } catch (\Exception $e) {
                // Re-enqueue
                array_unshift($body, $class);
                call_user_func_array(array('static', 'enqueue'), $body);
                echo "Failed: {$class}: {$value}. Re-enqueue\n";
            }
        }
    }

}
