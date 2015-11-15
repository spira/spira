<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spira\Rate\Model\Rating;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Rating::getTableName(), function (Blueprint $table) {
            $table->uuid('rating_id')->primary();
            $table->uuid('rateable_id');
            $table->string('rateable_type');
            $table->tinyInteger('rating_value');
            $table->uuid('user_id')->index();

            $table->unique(['rateable_id', 'rateable_type', 'user_id']);

            $table->foreign('user_id')
                ->references('user_id')->on(User::getTableName())
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(Rating::getTableName());
    }
}
