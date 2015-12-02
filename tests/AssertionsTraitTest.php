<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests;

use PHPUnit_Framework_AssertionFailedError;

/**
 * Class AssertionsTraitTest.
 */
class AssertionsTraitTest extends TestCase
{
    /**
     * Test that date formats are valid.
     */
    public function testValidISO8601Dates()
    {
        $this->assertValidIso8601Date('2015-07-02');
        $this->assertValidIso8601Date('2015-07-02T14:47:47+00:00');
        $this->assertValidIso8601Date('2015-07-02T14:47:47Z');
        $this->assertValidIso8601Date('20090621T0545Z');
    }

    /**
     * Test that date formats are invalid.
     *
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testInvalidISO8601Dates()
    {
        $this->assertValidIso8601Date('22/10/1990');
    }

    /**
     * Test that date formats that PHP considers invalid also fail the tests.
     *
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testValidISO8601DateInvalidDateTimeString()
    {
        $this->assertValidIso8601Date('2010-02-18T16:23:48,3-06:00'); //actually valid ISO8601, but php's DateTime() cant parse it
    }

    /**
     * Test that the response status assertion outputs the dumped json response on failure.
     */
    public function testFailingStatusCodeAssertion()
    {
        $this->getJson('/not-a-route');

        ob_start();
        try {
            $this->assertResponseStatus(200);
        } catch (\Exception $e) {
            $this->assertInstanceOf('PHPUnit_Framework_AssertionFailedError', $e);
        }
        $output = ob_get_clean();

        $this->assertContains('NotFoundHttpException', $output);
    }
}
