<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Class UtilityTest.
 * @group integration
 */
class UtilityTest extends TestCase
{
    public function testGetSystemInformationData()
    {
        $this->withAdminAuthorization()->getJson('/utility/system-information');
        $this->assertResponseStatus(200);
        $response = $this->getJsonResponse();

        $this->assertObjectHasAttribute('appBuildDate', $response);
    }

    public function testGetSystemInformationDataDenied()
    {
        $this->withAuthorization()->getJson('/utility/system-information');
        $this->assertResponseStatus(403);
    }
}
