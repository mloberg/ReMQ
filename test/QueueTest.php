<?php

require __DIR__ . '/../vendor/autoload.php';

use ReMQ\Queue;

class QueueTest extends PHPUnit_Framework_TestCase
{

    protected $q;
    protected $redisStub;

    protected function setUp()
    {
        $this->redisStub = $this->getMock('stdClass', array('rpush'));
        $this->q = new Queue('test');
        $this->q->setRedis($this->redisStub);
    }

    public function testEnqueue()
    {
        $q = $this->q;
        $redisStub = $this->redisStub;
        $redisStub->expects($this->once())
                  ->method('rpush')
                  ->with($this->equalTo('remq:test'),
                         $this->equalTo(json_encode(array('TestJob', 'foo', 'bar'))))
                  ->will($this->returnValue(true));
        $this->assertTrue($q->enqueue(TestJob, 'foo', 'bar'));
    }

    /**
     * @expectedException ReMQ\BadJobException
     */
    public function testWillThrowExceptionOnBadJob()
    {
        $this->q->enqueue(stdClass);
    }

}
