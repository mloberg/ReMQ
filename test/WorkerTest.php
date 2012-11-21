<?php

require __DIR__ . '/../vendor/autoload.php';

use ReMQ\Worker;

class WorkerTest extends PHPUnit_Framework_TestCase
{

    protected $w;
    protected $redisStub;

    protected function setUp()
    {
        $this->redisStub = $this->getMock('stdClass', array('keys', 'blpop', 'rpush'));
        $this->w = new Worker();
        $this->w->setRedis($this->redisStub);
    }

    private function redisReturnsEmptyArray()
    {
        $this->redisStub->expects($this->any())
                        ->method('keys')
                        ->will($this->returnValue(array()));
    }

    public function testAddQueue()
    {
        $this->redisReturnsEmptyArray();
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
        $this->redisReturnsEmptyArray();
        $w = $this->w;
        $w->addQueue('example');
        $w->addQueue('test');
        $this->assertContains('example', $w->queues());
        $w->removeQueue('example');
        $this->assertNotContains('example', $w->queues());
    }

    public function testWillAddAllQueues()
    {
        $w = $this->w;
        $redisStub = $this->redisStub;
        $redisStub->expects($this->once())
                  ->method('keys')
                  ->will($this->returnValue(array('remq:foo', 'remq:bar')));
        $w->addQueue('*');
        $this->assertContains('foo', $w->queues());
        $this->assertContains('bar', $w->queues());
    }

    public function testProcess()
    {
        $w = $this->w;
        $redisStub = $this->redisStub;
        TestJob::setPHPUnit($this);
        $jobInfo = array('TestJob', 'foo', 'bar');
        $redisStub->expects($this->once())
                  ->method('blpop')
                  ->will($this->returnValue(array('example', json_encode($jobInfo))));
        $w->run(REMQ_RUN_COUNT, 1);
    }

    /**
     * @expectedException FailedJobException
     */
    public function testReEnqueueAfterException()
    {
        $w = $this->w;
        $redisStub = $this->redisStub;
        $jobInfo = json_encode(array('FailingJob'));
        $redisStub->expects($this->once())
                  ->method('rpush')
                  ->with('example', $jobInfo);
        $redisStub->expects($this->once())
                  ->method('blpop')
                  ->will($this->returnValue(array('example', $jobInfo)));
        $w->runCount(1);
    }

    /**
     * @expectedException ReMQ\BadJobException
     */
    public function testThrowsExceptionIfNotValidJob()
    {
        $w = $this->w;
        $redisStub = $this->redisStub;
        $jobInfo = json_encode(array('BadJob'));
        $redisStub->expects($this->once())
                  ->method('blpop')
                  ->will($this->returnValue(array('example', $jobInfo)));
        $w->runCount(1);
    }

}
