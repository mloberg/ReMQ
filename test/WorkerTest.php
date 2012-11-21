<?php

require __DIR__ . '/../vendor/autoload.php';

use ReMQ\Worker;

class WorkerTest extends PHPUnit_Framework_TestCase
{

    protected $w;
    protected $redisStub;

    protected function setUp()
    {
        $this->redisStub = $this->getMock('stdClass', array('blpop'));
        $this->w = new Worker();
        $this->w->setRedis($this->redisStub);
    }

    public function testAddQueue()
    {
        $w = $this->w;
        $w->addQueue('example');
        $this->assertContains('example', $w->queues());
        $w->addQueue('test');
        $this->assertContains('test', $w->queues());
        // assert that the previos queue is still in place
        $this->assertContains('example', $w->queues());
    }

    public function testRemoveQueue()
    {
        $w = $this->w;
        $w->addQueue('example');
        $w->addQueue('test');
        $this->assertContains('example', $w->queues());
        $w->removeQueue('example');
        $this->assertNotContains('example', $w->queues());
    }

    public function testProcess()
    {
        $w = $this->w;
        $redisStub = $this->redisStub;
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

}
