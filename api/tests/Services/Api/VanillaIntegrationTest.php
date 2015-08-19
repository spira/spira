<?php

use App\Services\Api\Vanilla\Client;

class VanillaIntegrationTest extends TestCase
{
    // Exceptions

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function shouldNotGetApiInstance()
    {
        $client = App::make(Client::class);

        $test = $client->api('do_not_exist');
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function shouldNotCreateWithoutAuthorizedUser()
    {
        $client = App::make(Client::class);

        $client->setUser('no_user');

        $discussion = $client->api('discussions')->create('Foo', 'Bar', 1);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function shouldNotCreateEmptyDiscussion()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->create('', '', 1);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function shouldNotFindNonExistingDiscussion()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->find(0);
    }

    /**
     * @test
     *
     * @expectedException Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    public function shouldNotFindDiscussionWithInvalidIdFormat()
    {
        $client = App::make(Client::class);

        $discussion = $client->api('discussions')->find('foobar');
    }
}
