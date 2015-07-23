<?php

use Illuminate\Support\Facades\Queue;
use Pheanstalk\Pheanstalk;

class QueueTest extends TestCase
{
    protected $pheanstalk;
    protected $originalDriver;

    public function setUp()
    {
        parent::setUp();

        // We'll use the beanstalkd queue driver for this test.
        $this->originalDriver = getenv('QUEUE_DRIVER');
        var_dump($this->originalDriver);
        putenv('QUEUE_DRIVER=beanstalkd');

        $this->pheanstalk = new Pheanstalk(env('BEANSTALKD_HOST'));
    }

    public function teardown()
    {
        parent::teardown();

        // Restore the original driver
        putenv('QUEUE_DRIVER='.$this->originalDriver);
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
