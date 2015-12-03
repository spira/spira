<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\tests;

use Illuminate\Support\Facades\Cache;

class CacheTest extends TestCase
{
    private static $cacheKey = 'foo';
    private static $cacheValue = 'bar';

    /**
     * Add key-value pair to the cache.
     */
    public function setUp()
    {
        parent::setUp();

        Cache::forever(self::$cacheKey, self::$cacheValue);
    }

    /**
     * Test Cache facade connection to the cache driver.
     *
     * @return void
     */
    public function testCacheHasKey()
    {
        $this->assertTrue(Cache::has(self::$cacheKey), 'Cache has key');
    }

    /**
     * Test cache key has value.
     *
     * @return void
     */
    public function testCacheHasVaue()
    {
        $retrievedKey = Cache::get(self::$cacheKey);

        $this->assertEquals(self::$cacheValue, $retrievedKey, 'Cache has correct value for key');
    }

    /**
     * Test cache key can be deleted.
     *
     * @return void
     */
    public function testCacheDelete()
    {
        Cache::forget(self::$cacheKey);

        $this->assertFalse(Cache::has(self::$cacheKey), 'Cache does not have key any more');
    }
}
