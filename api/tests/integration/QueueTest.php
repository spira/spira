<?php

use Illuminate\Support\Facades\Queue;
use Pheanstalk\Pheanstalk;

class QueueTest extends TestCase
{
    protected $pheanstalk;

    public function setUp()
    {
        parent::setUp();

        // We'll use the beanstalkd queue driver for this test.
        putenv('QUEUE_DRIVER=beanstalkd');

        $this->pheanstalk = new Pheanstalk(env('BEANSTALKD_HOST'));
    }

    /**
     * Test queue is listening.
     */
    public function testQueueConnection()
    {
        $this->assertTrue($this->pheanstalk->getConnection()->isServiceListening());
    }

    /**
     * Test adding to the queue.
     */
    public function testQueueAdd()
    {
        Queue::push(function ($job) {

            factory(App\Models\TestEntity::class)->create();

            $job->delete();
        });

        $job = $this->pheanstalk
            ->watch('default')
            ->reserve();

        $data = $job->getData();
        $decoded = json_decode($data);

        $this->assertEquals('IlluminateQueueClosure', $decoded->job);
    }
}
