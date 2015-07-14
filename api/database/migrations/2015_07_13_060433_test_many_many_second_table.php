<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TestManyManySecondTable extends Migration
{
    const MODEL = 'App\Models\SecondTestEntity';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelClass = static::MODEL;
        Schema::create($modelClass::getTableName(), function(Blueprint $table)
        {
            $table->uuid('entity_id');
            $table->uuid('check_entity_id');
            $table->primary('entity_id');
            $table->string('value', 255);
        }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $modelClass = static::MODEL;

        Schema::drop($modelClass::getTableName());
    }
}
