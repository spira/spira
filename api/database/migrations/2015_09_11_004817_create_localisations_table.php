<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocalisationsTable extends Migration
{
    const TABLE_NAME = 'localisations';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('entity_id');
            $table->char('region_code', 2);
            $table->json('localisations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(static::TABLE_NAME);
    }
}
