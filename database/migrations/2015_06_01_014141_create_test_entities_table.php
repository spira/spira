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
use Spira\Core\Model\Test\TestEntity;

class CreateTestEntitiesTable extends Migration
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
        Schema::create(TestEntity::getTableName(), function (Blueprint $table) {
                $table->uuid('entity_id');
                $table->string('varchar', 255);
                $table->char('hash', 60);
                $table->integer('integer');
                $table->decimal('decimal', 11, 2);
                $table->boolean('boolean');
                $table->boolean('nullable')->nullable();
                $table->text('text');
                $table->date('date');
                $table->boolean('multi_word_column_title');
                $table->boolean('hidden');
                $table->json('json');

                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->primary('entity_id');
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
        if (env('APP_ENV') !== 'spira-core-testing') {
            return true;
        }
        Schema::drop(TestEntity::getTableName());
    }
}
