# ReMQ

[![Build Status](https://secure.travis-ci.org/mloberg/ReMQ.png?branch=master)](https://travis-ci.org/mloberg/ReMQ)

Redis Message Queue (ReMQ) is a message queue built on top of the awesome Redis key-value store.

## Jobs

Jobs are stored as classes. The class must have a perform method which can take a variable number of parameters.

	class JobClass
	{

		static function perform($param1, $param2)
		{
			echo "Ran job with {$param1} and {$param2}.";
		}

	}

## Queuing Jobs

Instead of creating a queue for each job, ReMQ allows multiple jobs per queue. This is for simplicity's sake, and there is no other reason behind it.

	$queue = new ReMQ\Queue('name');
	$queue->enqueue(JobClass, 'param1', 'param2');

## Processing Jobs

To process a job, you need to create a worker for the queue.

	$worker = new ReMQ\Worker('name');

You can also add additional queues to process.

	$worker->addQueue('other');

You can also match queue names.

	$worker->addQueue('*');
	$worker->addQueue('namespaced:*');

To run the worker, you will call *run*, *runTime*, *runCount*, or *runForever*.

	$worker->runTime(60); // run the worker for 60 seconds. REMQ_RUN_TIME
	$worker->runCount(10); // run 10 jobs. REMQ_RUN_COUNT
	$worker->runForever(); // run until the script is killed. REMQ_RUN_FOREVER
	$worker->run(REMQ_RUN_TYPE, [$unit]); // default is REMQ_RUN_FOREVER

## Failing Jobs

If an exception is thrown when a job is being processed, the job will be re-enqueued, and the exception rethrown.

	try {
		$worker->runForever();
	} Catch (Exception $e) {
		echo $e->getMessage();
	}

#### TODO:

* Handle errors.

## Redis

ReMQ is using [Predis](https://github.com/nrk/predis) to connect with Redis. By default Predis will connect to 127.0.0.1 on port 6379. If you are running Redis on another machine, or a non-standard port, you can define that using the setRedisConfig method.

	$queue->setRedisConfig(array(
		'host' => '10.0.0.1'
	));

If Redis has an auth password, you will need to call the auth command before queuing or processing any jobs.

	$queue->redis()->auth('your-pass');
