<?php


use Illuminate\Support\Facades\Artisan;

class DocumentationTest extends TestCase
{

    public function testDocumentation()
    {

        $exitCode = Artisan::call('apiary:validate');

        $this->assertEquals(0, $exitCode);
    }

}
