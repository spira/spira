<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Spira\Core\Model\Test\TestEntity;

class CreateElasticSearchIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! TestEntity::indexExists()) {
            $testEntity = new TestEntity;

            $settings = [
                'index' => $testEntity->getIndexName(),
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'filter' => [
                                'autocomplete_filter' => [
                                    'type' => 'edge_ngram',
                                    'min_gram' => 1,
                                    'max_gram' => 20,
                                ],
                            ],
                            'analyzer' => [
                                'autocomplete' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'standard',
                                    'filter' => [
                                        'lowercase',
                                        'autocomplete_filter',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $testEntity->getElasticSearchClient()->indices()->create($settings);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (TestEntity::indexExists()) {
            TestEntity::deleteIndex();
        }
    }
}
