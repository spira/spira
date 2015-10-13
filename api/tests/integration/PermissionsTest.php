<?php


class PermissionsTest extends TestCase
{
    public function testAdminGetRoles()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);

        $someUser = $this->createUser();
        $this->assignTest($someUser);

        $token = $this->tokenFromUser($adminUser);

        $this->getJson('/permissions/user/'.$someUser->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $result = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertTrue(in_array('user', $result),'check if user has default role');
        $this->assertTrue(in_array('testrole', $result),'check if user has assigned test role');
    }

    public function testAdminGetSelfRoles()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $this->assignTest($adminUser);

        $token = $this->tokenFromUser($adminUser);

        $this->getJson('/permissions/user/'.$adminUser->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $result = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertTrue(in_array('user', $result),'check if user has default role');
        $this->assertTrue(in_array('testrole', $result),'check if user has assigned test role');
    }

    public function testGuestGetRoles()
    {
        $notAdminUser = $this->createUser();

        $someUser = $this->createUser();
        $this->assignTest($someUser);

        $token = $this->tokenFromUser($notAdminUser);

        $this->getJson('/permissions/user/'.$someUser->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGuestGetSelfRoles()
    {
        $notAdminUser = $this->createUser();
        $this->assignTest($notAdminUser);

        $token = $this->tokenFromUser($notAdminUser);

        $this->getJson('/permissions/user/'.$notAdminUser->user_id, [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ]);

        $result = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $this->assertTrue(in_array('user', $result),'check if user has default role');
        $this->assertTrue(in_array('testrole', $result),'check if user has assigned test role');
    }
}