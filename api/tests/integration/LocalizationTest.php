<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Localization;

/**
 * Class LocalizationTest.
 *
 * @group integration
 */
class LocalizationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetOneAttribute()
    {
        $entity = factory(Localization::class)->create();
        $localizations = json_decode($entity->localizations, true);

        $this->getJson(sprintf(
            '/localizations/%s/%s/%s',
            $entity->region_code,
            $entity->entity_id,
            $attribute = key($localizations)
        ));

        $attributes = json_decode($this->response->getContent(), true);
        print_r($attributes);

        $this->assertCount(1, $attributes);
        $this->assertEquals($localizations[$attribute], $attributes[$attribute]);
    }

    /**
     * @test
     */
    public function shouldGetAllAttributes()
    {
        $entity = factory(Localization::class)->create();
        $localizations = json_decode($entity->localizations, true);

        $this->getJson(sprintf(
            '/localizations/%s/%s',
            $entity->region_code,
            $entity->entity_id
        ));

        $attributes = json_decode($this->response->getContent(), true);

        $this->assertEquals($localizations['varchar'], $attributes['varchar']);
        $this->assertEquals($localizations['text'], $attributes['text']);
    }
}
