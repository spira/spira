<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Class PermissionsTest.
 * @group integration
 */
class PermissionsTest extends TestCase
{
    public function testNotAuthManyRoles()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $this->assignTest($adminUser);

        $this->getJson('/users/'.$adminUser->user_id.'/roles');
        $this->assertException('Unauthorized', 401, 'UnauthorizedException');
    }

    public function testStructureFromPermissionsRequest()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $this->assignTest($adminUser);

        $token = $this->tokenFromUser($adminUser);
        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$adminUser->user_id.'/roles', [
            'With-Nested' => 'permissions',
        ]);

        $result = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $result = $this->prepareArray($result);
        $this->assertTrue(isset($result['user']), 'check if user has default role');
        $this->assertTrue(isset($result['testrole']), 'check if user has assigned test role');
        $this->assertTrue(isset($result['admin']), 'check if user has inherited admin role');
        $this->assertTrue(isset($result['superAdmin']), 'check if user has assigned superAdmin role');

        $this->assertObjectHasAttribute('_permissions', $result['admin']);

        $structureCheck = false;
        foreach ($result['admin']->_permissions as $permission) {
            if (
                $permission->key === 'App\Http\Controllers\PermissionsController@getAll' &&
                property_exists($permission, 'matchingRoutes') &&
                is_array($permission->matchingRoutes) &&
                $permission->matchingRoutes[0]->method === 'GET' &&
                $permission->matchingRoutes[0]->uri === '/users/{id}/roles'
            ) {
                $structureCheck = true;
            }
        }

        $this->assertTrue($structureCheck);
    }

    public function testStructureFromUserRequest()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $this->assignTest($adminUser);

        $token = $this->tokenFromUser($adminUser);
        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$adminUser->user_id, [
            'With-Nested' => 'roles.permissions',
        ]);

        $result = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $result = $this->prepareArray($result->_roles);
        $this->assertTrue(isset($result['user']), 'check if user has default role');
        $this->assertTrue(isset($result['testrole']), 'check if user has assigned test role');
        $this->assertTrue(isset($result['admin']), 'check if user has inherited admin role');
        $this->assertTrue(isset($result['superAdmin']), 'check if user has assigned superAdmin role');

        $this->assertObjectHasAttribute('_permissions', $result['admin']);

        $structureCheck = false;
        foreach ($result['admin']->_permissions as $permission) {
            if (
                $permission->key === 'App\Http\Controllers\PermissionsController@getAll' &&
                property_exists($permission, 'matchingRoutes') &&
                is_array($permission->matchingRoutes) &&
                $permission->matchingRoutes[0]->method === 'GET' &&
                $permission->matchingRoutes[0]->uri === '/users/{id}/roles'
            ) {
                $structureCheck = true;
            }
        }

        $this->assertTrue($structureCheck);
    }

    public function testManyRoles()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $this->assignTest($adminUser);

        $token = $this->tokenFromUser($adminUser);
        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$adminUser->user_id.'/roles', [
            'With-Nested' => 'permissions',
        ]);

        $result = json_decode($this->response->getContent());
        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();

        $result = $this->prepareArray($result);
        $this->assertTrue(isset($result['user']), 'check if user has default role');
        $this->assertTrue(isset($result['testrole']), 'check if user has assigned test role');
        $this->assertTrue(isset($result['admin']), 'check if user has inherited admin role');
        $this->assertTrue(isset($result['superAdmin']), 'check if user has assigned superAdmin role');
    }

    public function testAdminGetRoles()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);

        $someUser = $this->createUser();
        $this->assignTest($someUser);

        $token = $this->tokenFromUser($adminUser);

        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$someUser->user_id.'/roles');

        $result = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $result = $this->prepareArray($result);
        $this->assertTrue(isset($result['user']), 'check if user has default role');
        $this->assertTrue(isset($result['testrole']), 'check if user has assigned test role');
    }

    public function testAdminGetSelfRoles()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $this->assignTest($adminUser);

        $token = $this->tokenFromUser($adminUser);

        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$adminUser->user_id.'/roles');

        $result = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $result = $this->prepareArray($result);
        $this->assertTrue(isset($result['user']), 'check if user has default role');
        $this->assertTrue(isset($result['testrole']), 'check if user has assigned test role');
    }

    public function testGuestGetRoles()
    {
        $notAdminUser = $this->createUser();

        $someUser = $this->createUser();
        $this->assignTest($someUser);

        $token = $this->tokenFromUser($notAdminUser);

        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$someUser->user_id.'/roles');

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGuestGetSelfRoles()
    {
        $notAdminUser = $this->createUser();
        $this->assignTest($notAdminUser);

        $token = $this->tokenFromUser($notAdminUser);

        $this->withAuthorization('Bearer '.$token)->getJson('/users/'.$notAdminUser->user_id.'/roles');

        $result = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->shouldReturnJson();
        $this->assertJsonArray();
        $result = $this->prepareArray($result);
        $this->assertTrue(isset($result['user']), 'check if user has default role');
        $this->assertTrue(isset($result['testrole']), 'check if user has assigned test role');
    }

    public function testAdminAssignNonExistingRoles()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->customize(['key' => 'non-existing-role'])
            ->transformed();

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $object = json_decode($this->response->getContent());

        $this->assertCount(2, $object->invalid);
        $this->assertObjectHasAttribute('key', $object->invalid[0]);
        $this->assertObjectHasAttribute('key', $object->invalid[1]);
        $this->assertEquals('The key must be an existing Rbac role', $object->invalid[0]->key[0]->message);
        $this->assertEquals('The key must be an existing Rbac role', $object->invalid[1]->key[0]->message);
    }

    public function testAdminAssignSimpleRoles()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = 'user';

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertResponseStatus(201);
    }

    public function testAdminAssignAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = \App\Models\Role::ADMIN_ROLE;

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testAdminAssignSuperAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = \App\Models\Role::SUPER_ADMIN_ROLE;

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testAdminDetachAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = 'user';

        $someUser = $this->createUser();
        $this->assignAdmin($someUser);

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testAdminDetachSuperAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = 'user';

        $someUser = $this->createUser();
        $this->assignSuperAdmin($someUser);

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testGuestUserAssignSimpleRoles()
    {
        $nonAdminUser = $this->createUser();
        $token = $this->tokenFromUser($nonAdminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = 'user';

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertException('Denied', 403, 'ForbiddenException');
    }

    public function testSuperAdminAssignAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = \App\Models\Role::ADMIN_ROLE;

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertResponseStatus(201);
    }

    public function testSuperAdminAssignSuperAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = \App\Models\Role::SUPER_ADMIN_ROLE;

        $someUser = $this->createUser();

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertResponseStatus(201);
    }

    public function testSuperAdminDetachAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = 'user';

        $someUser = $this->createUser();
        $this->assignAdmin($someUser);

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertResponseStatus(201);
    }

    public function testSuperAdminDetachSuperAdminRole()
    {
        $adminUser = $this->createUser();
        $this->assignSuperAdmin($adminUser);
        $token = $this->tokenFromUser($adminUser);

        $roles = $this->getFactory(\App\Models\Role::class)
            ->count(2)
            ->transformed();

        $roles[0]['key'] = 'testrole';
        $roles[1]['key'] = 'user';

        $someUser = $this->createUser();
        $this->assignSuperAdmin($someUser);

        $this->withAuthorization('Bearer '.$token)->putJson('/users/'.$someUser->user_id.'/roles', $roles);

        $this->assertResponseStatus(201);
    }

    protected function prepareArray($roles)
    {
        $newArray = [];
        foreach ($roles as $role) {
            $newArray[$role->key] = $role;
        }

        return $newArray;
    }
}
