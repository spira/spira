<?php

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
        if(!TestEntity::indexExists()) {
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
        if(TestEntity::indexExists()) {
            TestEntity::deleteIndex();
        }
    }
}
