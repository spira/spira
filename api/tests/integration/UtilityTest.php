<?php

/**
 * Class UtilityTest
 * @group integration
 * @group testing
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
