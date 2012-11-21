<?php

namespace ReMQ;

class BadJobException extends \Exception { }

abstract class ReMQ
{

    /**
     * The Redis connection object.
     * @var object Redis connection
     */
    protected $redis = null;

    /**
     * Connect to Redis with non-standard settings.
     *
     * @param array $config Redis connection options
     */
    public function setRedisConfig($config) {
        $this->redis = new Redis($config);
    }

    /**
     * Set a custom Redis connection.
     *
     * @param object $redis Custom Redis connection object
     */
    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    /**
     * Return the Redis connection object.
     *
     * Instantiate the connection if it doesn't exist.
     *
     * @return object Redis connection
     */
    public function redis()
    {
        if (!$this->redis) {
            $this->redis = new Redis();
        }
        return $this->redis;
    }

    /**
     * Return a normalized ReMQ queue name.
     *
     * @param string $name Queue name
     * @return string Normalized name
     */
    protected function normalizeQueueName($name)
    {
        return 'remq:' . $name;
    }

    /**
     * Check if the job is valid (has process method).
     *
     * @param string $class Class name
     * @throws BadJobException if the job isn't valid
     * @return boolean True if job is valid
     */
    protected function isValidJob($class)
    {
        if (!method_exists($class, 'perform')) {
            throw new BadJobException($class . ' is not a valid job');
            return false;
        }
        return true;
    }

}
