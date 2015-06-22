<?php

use App\Models\TestEntity;
use Illuminate\Support\Facades\Queue;

class QueueTest extends TestCase
{


    /**
     * Test the connection to the queue runner
     */
    public function testQueue()
    {

        $userCountBefore = TestEntity::count();

        Queue::push(function($job){

            TestEntity::fakeTestEntity();

            $job->delete();
        });

        sleep(5); //wait for the queue process to run @todo find a way to halt until the runner has been processed rather than guessing

        $userCountAfter = TestEntity::count();

        $this->assertNotEquals($userCountAfter, $userCountBefore, 'User count has changed');


    }



}
