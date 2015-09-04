<?php


use Illuminate\Support\Facades\Artisan;

/**
 * Class DocumentationTest.
 * @group integration
 */
class DocumentationTest extends TestCase
{
    /**
     * Index page (Apiary documentation) test.
     */
    public function testIndexPage()
    {
        $this->getJson('/');

        $this->assertResponseOk();

        $this->see('<title>Spira - API Documentation</title>');
    }

    public function testDocumentationApib()
    {
        $this->getJson('/documentation.apib');

        $this->assertResponseOk();
        $this->see('FORMAT: 1A'); //see apiary format is present
    }

    public function testDocumentationIsValid()
    {
        $exitCode = Artisan::call('apiary:validate');

        $this->assertEquals(0, $exitCode);
    }
}
