<?php

use App\Services\Api\Vanilla\Client;

class VanillaTest extends TestCase
{
    // Exceptions
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

    public function testUnauthorizedAccess()
    {
        $this->setExpectedExceptionRegExp(
            Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException::class,
            '/unauthorized/i',
            0
        );

        $client = App::make(Client::class);

        $client->setUser('foobar');

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
    }

    public function testBadRequest()
    {
        $this->setExpectedExceptionRegExp(
            Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class,
            '/bad request/i',
            0
        );

        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('', '', 1);
    }

    public function testNotFound()
    {
        $this->setExpectedExceptionRegExp(
            Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            '/not found/i',
            0
        );

        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->find(0);
    }

    public function testNotAllowed()
    {
        $this->setExpectedExceptionRegExp(
            Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            '/method not allowed/i',
            0
        );

        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->find('foobar');
    }

    // Generic
    public function testDifferentUser()
    {
        $client = App::make(Client::class);

        $client->setUser('admin');

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);

        $this->assertEquals('admin', $discussion['Discussion']['InsertName']);
    }

    // Configuration
    public function testConfigurationCurrent()
    {
        $client = App::make(Client::class);

        $current = $client->api('configuration')->current();

        $this->assertArrayHasKey('Title', $current['Configuration']);
    }

    // Discussions
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

    // Comments
    public function testCreateComment()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $comment = $client->api('comments')->create($id, 'foobar');

        $this->assertEquals($id, $comment['Comment']['DiscussionID']);
    }

    public function testUpdateComment()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $comment = $client->api('comments')->create($id, 'foobar');
        $id = $comment['Comment']['CommentID'];

        $comment = $client->api('comments')->update($id, 'barfoo');

        $this->assertEquals('barfoo', $comment['Comment']['Body']);
    }

    public function testRemoveComment()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        $comment = $client->api('comments')->create($discussionId, 'foobar');
        $id = $comment['Comment']['CommentID'];

        $comment = $client->api('comments')->remove($id);

        $discussion = $client->api('discussions')->find($discussionId);

        $this->assertCount(0, $discussion['Comments']);
    }
}
