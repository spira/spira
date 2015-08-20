<?php

use App\Services\Api\Vanilla\Client;
use App\Services\Api\Vanilla\Api\User;
use App\Services\Api\Vanilla\Api\Comment;
use App\Services\Api\Vanilla\Api\Discussion;
use App\Services\Api\Vanilla\Api\Configuration;

class VanillaApiTest extends TestCase
{
    protected function getApiMock($apiClass)
    {
        $guzzleClient = $this->getMock('Guzzle\Http\Client', ['send']);
        $guzzleClient
            ->expects($this->any())
            ->method('send');

        $client = new Client($guzzleClient);

        return $this->getMockBuilder($apiClass)
            ->setMethods(['get', 'post', 'postRaw', 'delete', 'put'])
            ->setConstructorArgs([$client])
            ->getMock();
    }


    // Configuration

    /**
     * @test
     */
    public function shouldGetConfiguration()
    {
        $expected = [
            'Configuration' => [
                'Title' => 'Spira',
                'Domain' => ''
            ]
        ];

        $api = $this->getApiMock(Configuration::class);
        $api->expects($this->once())
            ->method('get')
            ->with('configuration')
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $api->current());
    }


    // Discussions

    /**
     * @test
     */
    public function shouldGetDiscussions()
    {
        $sentData = ['page' => 'p1'];

        $api = $this->getApiMock(Discussion::class);
        $api->expects($this->once())
            ->method('get')
            ->with('discussions', $sentData);

        $api->all(1);
    }

    /**
     * @test
     */
    public function shouldCreateDiscussion()
    {
        $data = [
            'Name' => $name = 'Some name',
            'Body' => $body = 'Some body',
            'CategoryID' => $categoryId = 1
        ];

        $api = $this->getApiMock(Discussion::class);
        $api->expects($this->once())
            ->method('post')
            ->with('discussions', $data);

        $api->create($name, $body, $categoryId);
    }

    /**
     * @test
     */
    public function shouldGetDiscussion()
    {
        $expected = [
            'Discussion' => ['DiscussionID' => 123],
            'CategoryID' => 1,
            'Category' => [],
            'Comments' => [],
            'Page' => 1
        ];

        $api = $this->getApiMock(Discussion::class);
        $api->expects($this->once())
            ->method('get')
            ->with('discussions/123', ['page' => 'p1'])
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $api->find(123));
    }

    /**
     * @test
     */
    public function shouldGetDiscussionByForeignId()
    {
        $expected = [
            'Discussion' => ['DiscussionID' => 123, 'ForeignID' => '7e57d004-2b97-0e7a-b45f-5387367791cd'],
            'CategoryID' => 1,
            'Category' => [],
            'Comments' => [],
            'Page' => 1
        ];

        $api = $this->getApiMock(Discussion::class);
        $api->expects($this->once())
            ->method('get')
            ->with('discussions/foreign/7e57d004-2b97-0e7a-b45f-5387367791cd', ['page' => 'p1', 'perPage' => 10])
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $api->findByForeignId('7e57d004-2b97-0e7a-b45f-5387367791cd'));
    }

    /**
     * @test
     */
    public function shouldUpdateDiscussion()
    {
        $input = [
            'Name' => $name = 'New name',
            'Body' => $body = 'New body'
        ];

        $api = $this->getApiMock(Discussion::class);
        $api->expects($this->once())
            ->method('put')
            ->with('discussions/123', $input);

        $api->update(123, $name, $body);
    }

    /**
     * @test
     */
    public function shouldRemoveDiscussion()
    {
        $api = $this->getApiMock(Discussion::class);

        $api->expects($this->once())
            ->method('delete')
            ->with('discussions/123');

        $api->remove(123);
    }

    /**
     * @test
     */
    public function shouldRemoveDiscussionByForeignId()
    {
        $api = $this->getApiMock(Discussion::class);

        $api->expects($this->once())
            ->method('delete')
            ->with('discussions/foreign/123');

        $api->removeByForeignId(123);
    }


    // Comments

    /**
     * @test
     */
    public function shouldCreateComment()
    {
        $input = [
            'Body' => $body = 'Some body',
            'Format' => 'Html'
        ];

        $api = $this->getApiMock(Comment::class);
        $api->expects($this->once())
            ->method('post')
            ->with('discussions/123/comments', $input);

        $api->create(123, $body);
    }

    /**
     * @test
     */
    public function shouldUpdateComment()
    {
        $input = [
            'Body' => $body = 'New body'
        ];

        $api = $this->getApiMock(Comment::class);
        $api->expects($this->once())
            ->method('put')
            ->with('discussions/comments/456', $input);

        $api->update(456, $body);
    }

    /**
     * @test
     */
    public function shouldRemoveComment()
    {
        $api = $this->getApiMock(Comment::class);

        $api->expects($this->once())
            ->method('delete')
            ->with('discussions/comments/456');

        $api->remove(456);
    }


    // Users

    /**
     * @test
     */
    public function shouldGetUsers()
    {
        $api = $this->getApiMock(User::class);
        $api->expects($this->once())
            ->method('get')
            ->with('users');

        $api->all();
    }

    /**
     * @test
     */
    public function shouldCreateUser()
    {
        $input = [
            'Name' => $username = 'Some name',
            'Email' => $email = 'some@email.com',
            'Password' => $password = 'password',
            'RoleID' => [8]
        ];

        $api = $this->getApiMock(User::class);
        $api->expects($this->once())
            ->method('post')
            ->with('users', $input);

        $api->create($username, $email, $password);
    }

    /**
     * @test
     */
    public function shouldLinkSsoUser()
    {
        $input = [
            'UniqueID' => $id = '123',
            'Name' => $username = 'Some name',
            'Email' => $email = 'some@email.com',
        ];

        $api = $this->getApiMock(User::class);
        $api->expects($this->once())
            ->method('post')
            ->with('users/sso', $input);

        $api->sso($id, $username, $email);
    }
}
