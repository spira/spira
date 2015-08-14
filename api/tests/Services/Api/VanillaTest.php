<?php

use App\Services\Api\Vanilla\Client;

class VanillaTest extends TestCase
{
    public function testInvalidApiGroup()
    {
        $this->setExpectedExceptionRegExp(
            InvalidArgumentException::class,
            '/foobar/i',
            0
        );

        $client = App::make(Client::class);
        $test = $client->api('foobar');
    }

    public function testConfigurationCurrent()
    {
        $client = App::make(Client::class);

        $current = $client->api('configuration')->current();

        $this->assertArrayHasKey('Title', $current['Configuration']);
    }

    public function testDiscussionsAll()
    {
        $client = App::make(Client::class);

        $all = $client->api('discussions')->all();

        $this->assertArrayHasKey('Discussions', $all);
    }

    public function testDiscussionsCreate()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);

        $this->assertArrayHasKey('Discussion', $discussion);
        $this->assertEquals('Discussion', $discussion['Type']);
    }

    public function testDiscussionsFind()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $discussion = $client->api('discussions')->find($id);

        $this->assertEquals($id, $discussion['Discussion']['DiscussionID']);
    }

    public function testDiscussionsUpdate()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $discussion = $client->api('discussions')->update($id, 'Foobar', 'Foo');

        $this->assertEquals('Foobar', $discussion['Discussion']['Name']);
    }

    public function testDiscussionsRemove()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $client->api('discussions')->remove($id);

        try {
            $discussion = $client->api('discussions')->find($id);
        } catch (Exception $e) {
            return;
        }

        $this->fail('The discussion was not removed.');
    }
}
