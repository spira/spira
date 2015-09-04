<?php

use Rhumsaa\Uuid\Uuid;
use App\Services\Api\Vanilla\Client;

class VanillaIntegrationTest extends TestCase
{
    /**
     * API client.
     *
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        parent::setUp();

        $this->client = App::make(Client::class);
    }

    // Exceptions

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function shouldNotGetApiInstance()
    {
        $test = $this->client->api('do_not_exist');
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function shouldNotCreateWithoutAuthorizedUser()
    {
        $this->client->setUser('no_user');

        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function shouldNotCreateEmptyDiscussion()
    {
        $discussion = $this->client->api('discussions')->create('', '', 1);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function shouldNotFindNonExistingDiscussion()
    {
        $discussion = $this->client->api('discussions')->find(0);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    public function shouldNotFindDiscussionWithInvalidIdFormat()
    {
        $discussion = $this->client->api('discussions')->find('foobar');
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
     */
    public function shouldNotAccessApiService()
    {
        $port = 'FORUMSERVER_PORT';

        $env = getenv($port);
        putenv($port.'=8888');

        $client = App::make(Client::class);

        // Restore the env variable
        putenv($port.'='.$env);

        $client->api('discussions')->all();
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function shouldHandleUnknownError()
    {
        $response = $this->getMock('Guzzle\Http\Message\Response', ['getStatusCode'], [420]);
        $response
            ->method('getStatusCode')
            ->willReturn(420);

        $request = $this->getMock('Guzzle\Http\Message\Request', [], [null, null]);
        $request
            ->method('getResponse')
            ->willReturn($response);

        $error = new App\Services\Api\Vanilla\Error;
        $event = new Guzzle\Common\Event;
        $event['request'] = $request;

        $error->onRequestError($event);
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionOnNoneErrors()
    {
        $response = $this->getMock('Guzzle\Http\Message\Response', [], [0]);
        $response
            ->method('isError')
            ->willReturn(false);

        $request = $this->getMock('Guzzle\Http\Message\Request', [], [null, null]);
        $request
            ->method('getResponse')
            ->willReturn($response);

        $error = new App\Services\Api\Vanilla\Error;
        $event = new Guzzle\Common\Event;
        $event['request'] = $request;

        $this->assertNull($error->onRequestError($event));
    }

    /**
     * @test
     */
    public function shouldReturnNoneJsonBodyUnmodified()
    {
        $body = 'foobar';

        $response = $this->getMock('Guzzle\Http\Message\Response', [], [0]);
        $response
            ->method('getBody')
            ->willReturn($body);

        $guzzleClient = $this->getMock('Guzzle\Http\Client', ['send']);
        $guzzleClient
            ->expects($this->any())
            ->method('send')
            ->willReturn($response);

        $client = new Client($guzzleClient);
        $response = $client->api('discussions')->all();

        $this->assertEquals($body, $response);
    }

    // Generic

    /**
     * @test
     */
    public function shouldCreateDiscussionWithAdminUser()
    {
        $this->client->setUser('admin');

        $discussion = $this->client->api('discussions')->create('Some name', 'Some body', 1);

        $this->assertEquals('admin', $discussion['Discussion']['InsertName']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    // Discussions

    /**
     * @test
     */
    public function shouldGetDiscussions()
    {
        $all = $this->client->api('discussions')->all();

        $this->assertArrayHasKey('Discussions', $all);
    }

    /**
     * @test
     */
    public function shouldCreateDiscussion()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);

        $this->assertArrayHasKey('Discussion', $discussion);
        $this->assertEquals('Discussion', $discussion['Type']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     */
    public function shouldGetDiscussion()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $discussion = $this->client->api('discussions')->find($id);

        $this->assertEquals($id, $discussion['Discussion']['DiscussionID']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     */
    public function shouldUpdateDiscussion()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $discussion = $this->client->api('discussions')->update($id, 'Foobar', 'Foo');

        $this->assertEquals('Foobar', $discussion['Discussion']['Name']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function shouldDeleteDiscussion()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $this->client->api('discussions')->remove($id);

        $this->client->api('discussions')->find($id);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function shouldDeleteDiscussionByForeignId()
    {
        $id = (string) Uuid::uuid4();
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1, ['ForeignID' => $id]);

        $this->client->api('discussions')->removeByForeignId($id);

        $this->client->api('discussions')->find($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     */
    public function shoulePaginateCommentsInDiscussion()
    {
        // Create discussion thread
        $id = (string) Uuid::uuid4();
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1, ['ForeignID' => $id]);
        for ($i = 0; $i < 20; $i++) {
            $comment = $this->client->api('comments')->create(
                $discussion['Discussion']['DiscussionID'],
                'Comment #'.$i
            );
        }

        $discussion = $this->client->api('discussions')->findByForeignId($id, 1);
        $this->assertEquals(20, $discussion['Discussion']['CountComments']);
        $this->assertEquals(10, $discussion['CommentsPerPage']);
        $this->assertEquals(1, $discussion['Page']);
        $this->assertCount(10, $discussion['Comments']);

        $discussion = $this->client->api('discussions')->findByForeignId($id, 2, 5);
        $this->assertEquals(20, $discussion['Discussion']['CountComments']);
        $this->assertEquals(5, $discussion['CommentsPerPage']);
        $this->assertEquals(2, $discussion['Page']);
        $this->assertCount(5, $discussion['Comments']);

        // Clean up
        $this->client->api('discussions')->removeByForeignId($id);
    }

    // Comments

    /**
     * @test
     */
    public function shouldCreateComment()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $comment = $this->client->api('comments')->create($id, 'foobar');

        $this->assertEquals($id, $comment['Comment']['DiscussionID']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     */
    public function shouldUpdateComment()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
        $id = $discussion['Discussion']['DiscussionID'];

        $comment = $this->client->api('comments')->create($id, 'foobar');
        $id = $comment['Comment']['CommentID'];

        $comment = $this->client->api('comments')->update($id, 'barfoo');

        $this->assertEquals('barfoo', $comment['Comment']['Body']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }

    /**
     * @test
     */
    public function shouldDeleteComment()
    {
        $discussion = $this->client->api('discussions')->create('Foo', 'Bar', 1);
        $discussionId = $discussion['Discussion']['DiscussionID'];

        $comment = $this->client->api('comments')->create($discussionId, 'foobar');
        $id = $comment['Comment']['CommentID'];

        $comment = $this->client->api('comments')->remove($id);

        $discussion = $this->client->api('discussions')->find($discussionId);

        $this->assertCount(0, $discussion['Comments']);

        // Clean up
        $this->client->api('discussions')->remove($discussion['Discussion']['DiscussionID']);
    }
}
