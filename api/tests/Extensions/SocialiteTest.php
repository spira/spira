<?php

use Laravel\Socialite\Two\User;
use App\Exceptions\NotImplementedException;
use App\Extensions\Socialite\Parsers\ParserFactory;

class SocialiteTest extends TestCase
{
    public function testParserFactoryUnknownParser()
    {
        $this->setExpectedExceptionRegExp(
            NotImplementedException::class,
            '/parser.*/',
            0
        );

        $user = new User;
        $socialUser = ParserFactory::parse($user, 'foobar');
    }
}
