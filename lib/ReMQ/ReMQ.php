<?php

namespace ReMQ;

abstract class ReMQ
{

    /**
     * The Redis connection object.
     * @var object Redis connection
     */
    private $redis = null;

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

}
