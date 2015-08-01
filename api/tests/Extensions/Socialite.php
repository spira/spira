<?php

use Laravel\Socialite\Two\User;
use App\Extensions\Socialite\Parsers\ParserFactory;

class Socialite extends TestCase
{
    public function testParserFactoryUnknownParser()
    {
        $this->setExpectedExceptionRegExp(
            'App\Exceptions\FatalErrorException',
            '/parser.*/',
            0
        );

        $user = new User;
        $socialUser = ParserFactory::parse($user, 'foobar');
    }
}
