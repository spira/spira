<?php

use Spira\Rbac\Item\Permission;

class RbacItemTest extends TestCase
{
    public function testAttachRule()
    {
        $item = new Permission('test');
        $rule = new AuthorRule();

        $item->attachRule($rule);

        $this->assertEquals(get_class($rule), $item->getRuleName());
    }

    public function testAttachOnlyOneRule()
    {
        $item = new Permission('test');
        $rule = new AuthorRule();

        $item->attachRule($rule);
        $this->setExpectedException('InvalidArgumentException','Only one rule can be attached, first detach the rule');
        $item->attachRule($rule);
    }

    public function testDetachRule()
    {
        $item = new Permission('test');
        $rule = new AuthorRule();

        $item->attachRule($rule);
        $item->detachRule();

        $this->assertNull($item->getRuleName());
    }
}