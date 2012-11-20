# ReMQ

Redis Message Queue (ReMQ) is a message queue built on top of the awesome Redis key-value store.

## Jobs

Jobs are stored as classes. The class must have a process method which can take a variable number of parameters.

	class JobClass
	{

		static function process($param1, $param2)
		{
			echo "Ran job with {$param1} and {$param2}.";
		}

	}

## Queuing Jobs

Instead of creating a queue for each job, ReMQ allows multiple jobs per queue. This is for simplicity's sake, and there is no other reason behind it.

	$queue = new ReMQ\Queue('name');
	$queue->enqueue(JobClass, 'param1', 'param2');

## Processing Jobs

To process a job, you need to create a worker, add the queues to process with the worker, and then finally process it.

	$worker = new ReMQ\Worker();
	$worker->addQueue('name');
	$worker->process();

## Redis

ReMQ is using [Predis](https://github.com/nrk/predis) to connect with Redis. By default Predis will connect to 127.0.0.1 on port 6379. If you are running Redis on another machine, or a non-standard port, you can define that using the setRedisConfig method.

	$queue->setRedisConfig(array(
		'host' => '10.0.0.1'
	));

If Redis has an auth password, you will need to call the auth command before queuing or processing any jobs.

	$queue->redis()->auth('your-pass');
