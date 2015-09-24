<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Spira\Model\Model\IndexedModel;

class ElasticSearchIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (IndexedModel::indexExists()) {
            IndexedModel::deleteIndex();
            Log::info('ElasticSearch index deleted');
        }
        IndexedModel::createIndex();
        Log::info('ElasticSearch index created');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        IndexedModel::deleteIndex();
        Log::info('ElasticSearch index deleted');
    }
}
