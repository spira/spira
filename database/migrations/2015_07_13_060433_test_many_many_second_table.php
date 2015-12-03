<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spira\Core\Model\Test\SecondTestEntity;

class TestManyManySecondTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APP_ENV') !== 'spira-core-testing') {
            return true;
        }
        Schema::create(SecondTestEntity::getTableName(), function (Blueprint $table) {
            $table->uuid('entity_id');
            $table->uuid('check_entity_id');
            $table->primary('entity_id');
            $table->string('value', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'spira-core-testing') {
            return true;
        }
        Schema::drop(SecondTestEntity::getTableName());
    }
}
