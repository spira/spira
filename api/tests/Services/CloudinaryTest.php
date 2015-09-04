<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * Class CloudinaryTest.
 * @group testing
 */
class CloudinaryTest extends TestCase
{
    public function testRequestSigning()
    {
        $cloudinary = new \App\Services\Cloudinary();

        $signature = $cloudinary->signRequestParams([
            'foo1' => 'bar1',
            'foo2' => 'bar2',
        ]);

        $this->assertInternalType('string', $signature);
        $this->assertEquals(sha1('foo1=bar1&foo2=bar2'.env('CLOUDINARY_API_SECRET')), $signature);
    }
}
