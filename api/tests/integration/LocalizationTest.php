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

    public function testShouldGetOneAttribute()
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

        $this->assertCount(1, $attributes);
        $this->assertEquals($localizations[$attribute], $attributes[$attribute]);
    }

    public function testShouldGetAllAttributes()
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

    /**
     * @group testing
     */
    public function testShouldPutOneAttribute()
    {
        $this->markTestIncomplete(
            'This test has not been correctly implemented yet.'
        );

        // First create a new localization
        $entity = factory(Localization::class)->create();
        $localizations = json_decode($entity->localizations, true);

        $firstLocalizationKey = key($localizations);
        $firstLocalizationValue = $localizations[$firstLocalizationKey];

        $newLocalizationValue = 'foobar';

        // Update one of the localization's parameters
        $this->putText(sprintf(
            '/localizations/%s/%s/%s',
            $entity->region_code,
            $entity->entity_id,
            $firstLocalizationKey
        ), $newLocalizationValue);
        $this->shouldReturnJson();

        // Check that only the attribute updated above has changed
        $updatedEntity = (new Localization)->findByCompoundKey(array('entity_id' => $entity->entity_id, 'region_code' => $entity->region_code));

        $this->assertResponseStatus(201);

    }

    /**
     * @group testing
     */
    public function testShouldPutOne()
    {
        $this->markTestIncomplete(
            'This test has not been correctly implemented yet.'
        );
    }
}
