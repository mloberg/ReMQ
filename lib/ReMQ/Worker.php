<?php

namespace ReMQ;

if (!defined('REMQ_RUN_FOREVER')) {
    define('REMQ_RUN_FOREVER', 1);
}
if (!defined('REMQ_RUN_TIME')) {
    define('REMQ_RUN_TIME', 2);
}
if (!defined('REMQ_RUN_COUNT')) {
    define('REMQ_RUN_COUNT', 3);
}

class BadRunTypeException extends \Exception { }

class Worker extends ReMQ
{

    /**
     * List of queues to process.
     * @var array Queues
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
        $match = $this->normalizeQueueName($match);
        return $this->redis()->keys($match);
    }

    /**
     * Add a queue to worker.
     *
     * @param string $name Queue to add
     */
    public function addQueue($name)
    {
        $queues = $this->findQueues($name);
        array_push($queues, $this->normalizeQueueName($name));
        $this->queues = array_unique(array_merge($this->queues, $queues));
    }

    /**
     * Remove queues from the worker.
     *
     * @param string $name Queue to remove
     */
    public function removeQueue($name)
    {
        foreach ($this->queues as $key => $value) {
            if ($value === $this->normalizeQueueName($name)) {
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
        return array_map(function($name) {
            return preg_replace('/^remq\:/', '', $name);
        }, $this->queues);
    }

    /**
     * Run the worker.
     *
     * @throws BadRunTypeException If unknown run type
     * @param integer $type Run type (REMQ_RUN_FORVER, REMQ_RUN_TIME, REMQ_RUN_COUNT)
     * @param integer $unit Run type measure
     */
    public function run($type = REMQ_RUN_FOREVER, $unit = null) {
        switch ($type) {
            case REMQ_RUN_FOREVER:
                $this->runForever();
                break;
            case REMQ_RUN_TIME:
                $this->runTime($unit);
                break;
            case REMQ_RUN_COUNT:
                $this->runCount($unit);
                break;
            default:
                throw new BadRunTypeException("Unknown run type {$type}");
                break;
        }
    }

    /**
     * Run the worker for a set period of time.
     *
     * @param integer $time Time to run worker
     */
    public function runTime($time)
    {
        // When we started running
        $start = time();
        // get the queues we're running
        $queues = $this->queues;
        // BLPOP needs a timeout parameteer
        array_push($queues, 1);
        // Process
        while (($start + $time) > time()) {
            $this->process($queues);
        }
    }

    /**
     * Run the worker a specified number of times.
     *
     * @param integer $count Number of times to run
     */
    public function runCount($count)
    {
        // Keep count
        $ran = 0;
        // get the queues we're running
        $queues = $this->queues;
        // BLPOP needs a timeout parameteer
        array_push($queues, 0);
        // Process
        while ($count > $ran) {
            $this->process($queues);
            $ran++;
        }
    }

    /**
     * Run the worker forever.
     */
    public function runForever()
    {
        // get the queues we're running
        $queues = $this->queues;
        // BLPOP needs a timeout parameteer
        array_push($queues, 0);
        while (true) {
            $this->process($queues);
        }
    }

    /**
     * Handle the Job processing.
     *
     * @todo Handle errors as well as exceptions
     *
     * @param array $queues List of queues to process
     */
    private function process($queues)
    {
        // Our Redis call
        $redis = array($this->redis(), 'blpop');
        try {
            list($queue, $job) = call_user_func_array($redis, $queues);
            // Make sure we have a valid return
            if ($job) {
                $body = json_decode($job);
                $class = array_shift($body);
                if ($this->isValidJob($class)) {
                    call_user_func_array(array($class, 'perform'), $body);
                }
            }
        } catch (\Exception $e) {
            // Re-enqueue
            $this->redis()->rpush($queue, $job);
            throw $e;
        }
    }

}
