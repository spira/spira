<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Queue;
use Pheanstalk\Pheanstalk;

/**
 * Class QueueTest.
 * @group integration
 */
class QueueTest extends TestCase
{
    protected $pheanstalk;
    protected $originalDriver;

    public function setUp()
    {
        parent::setUp();

        // We'll use the beanstalkd queue driver for this test.
        $this->originalDriver = getenv('QUEUE_DRIVER');
        putenv('QUEUE_DRIVER=beanstalkd');

        $this->pheanstalk = new Pheanstalk(env('BEANSTALKD_HOST'));
    }

    public function teardown()
    {
        parent::teardown();

        // Restore the original driver when the test is finished
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

            factory(Spira\Core\Model\Test\TestEntity::class)->create();

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
