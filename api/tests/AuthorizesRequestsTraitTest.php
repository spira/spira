<?php

use App\Extensions\Controller\AuthorizesRequestsTrait;
use App\Models\User;
use Illuminate\Auth\Access\Gate;
use Spira\Contract\Exception\ForbiddenException;

class AuthorizesRequestsTraitTest extends TestCase
{
    public function testAuthorize()
    {
        $gate = $this->getMockBuilder(Gate::class)
            ->disableOriginalConstructor()
            ->setMethods(['check'])
            ->getMock();

        $gate->expects($this->once())
            ->method('check')
            ->will($this->returnValue(true));

        /** @var AuthorizesRequestsTrait $trait */
        $trait = $this->getMockBuilder(AuthorizesRequestsTrait::class)
            ->setMethods(['getGate'])
            ->getMockForTrait();

        $trait->expects($this->once())
            ->method('getGate')
            ->will($this->returnValue($gate));


        $trait->authorize('check',[]);
    }

    public function testAuthorizeException()
    {
        $gate = $this->getMockBuilder(Gate::class)
            ->disableOriginalConstructor()
            ->setMethods(['check'])
            ->getMock();

        $gate->expects($this->once())
            ->method('check')
            ->will($this->returnValue(false));

        /** @var AuthorizesRequestsTrait $trait */
        $trait = $this->getMockBuilder(AuthorizesRequestsTrait::class)
            ->setMethods(['getGate'])
            ->getMockForTrait();

        $trait->expects($this->once())
            ->method('getGate')
            ->will($this->returnValue($gate));

        $this->setExpectedException(ForbiddenException::class, 'Denied.');
        $trait->authorize('check',[]);
    }

    public function testAuthorizeForUser()
    {
        $gate = $this->getMockBuilder(Gate::class)
            ->disableOriginalConstructor()
            ->setMethods(['check', 'forUser'])
            ->getMock();

        $gate->expects($this->once())
            ->method('check')
            ->will($this->returnValue(true));

        $gate->expects($this->once())
            ->method('forUser')
            ->will($this->returnSelf());

        /** @var AuthorizesRequestsTrait $trait */
        $trait = $this->getMockBuilder(AuthorizesRequestsTrait::class)
            ->setMethods(['getGate'])
            ->getMockForTrait();

        $trait->expects($this->once())
            ->method('getGate')
            ->will($this->returnValue($gate));


        $user = new User();
        $trait->authorizeForUser($user, 'check',[]);
    }

    public function testAuthorizeForUserException()
    {
        $gate = $this->getMockBuilder(Gate::class)
            ->disableOriginalConstructor()
            ->setMethods(['check', 'forUser'])
            ->getMock();

        $gate->expects($this->once())
            ->method('check')
            ->will($this->returnValue(false));

        $gate->expects($this->once())
            ->method('forUser')
            ->will($this->returnSelf());

        /** @var AuthorizesRequestsTrait $trait */
        $trait = $this->getMockBuilder(AuthorizesRequestsTrait::class)
            ->setMethods(['getGate'])
            ->getMockForTrait();

        $trait->expects($this->once())
            ->method('getGate')
            ->will($this->returnValue($gate));


        $user = new User();
        $this->setExpectedException(ForbiddenException::class, 'Denied.');
        $trait->authorizeForUser($user, 'check',[]);
    }

}