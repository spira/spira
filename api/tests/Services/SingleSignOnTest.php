<?php

use App\Exceptions\NotImplementedException;
use App\Services\SingleSignOn\SingleSignOnFactory;

class SingleSignOnTest extends TestCase
{
    public function testUnknownRequester()
    {
        $this->setExpectedExceptionRegExp(
            NotImplementedException::class,
            '/exists/',
            0
        );

        $request = Mockery::mock('Illuminate\Http\Request');
        $requester = SingleSignOnFactory::create('foobar', $request, null);
    }
}
