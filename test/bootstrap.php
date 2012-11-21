<?php

require __DIR__ . '/../vendor/autoload.php';

class TestJob
{

    private static $phpunit;

    static function setPHPUnit($phpunit)
    {
        static::$phpunit = $phpunit;
    }

    static function perform($foo, $bar)
    {
        static::$phpunit->assertEquals('foo', $foo);
        static::$phpunit->assertEquals('bar', $bar);
    }

}

class FailedJobException extends Exception { }

class FailingJob
{

    static function perform()
    {
        throw new FailedJobException('Job failed.');
    }

}

class BadJob { }
