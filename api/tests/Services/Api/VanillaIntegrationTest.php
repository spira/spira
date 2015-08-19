<?php

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
}
