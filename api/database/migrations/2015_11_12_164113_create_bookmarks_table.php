<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Bookmark;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookmarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Bookmark::getTableName(), function (Blueprint $table) {
            $table->uuid('bookmark_id')->primary();
            $table->uuid('bookmarkable_id');
            $table->enum('bookmarkable_type', Bookmark::$bookmarkables);
            $table->uuid('user_id')->index();

            $table->unique(['bookmarkable_id', 'bookmarkable_type', 'user_id']);

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
        Schema::drop(Bookmark::getTableName());
    }
}
