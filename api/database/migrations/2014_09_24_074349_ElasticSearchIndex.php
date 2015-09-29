<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use App\Models\TestEntity;

class ElasticSearchIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! TestEntity::indexExists()) {
            TestEntity::createIndex(); // Creates the default index as specified in api/config/elasticquent.php
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
