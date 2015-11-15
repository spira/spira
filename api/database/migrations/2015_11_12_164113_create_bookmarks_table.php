<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spira\Bookmark\Model\Bookmark;

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
            $table->string('bookmarkable_type');
            $table->uuid('user_id')->index();

            $table->unique(['bookmarkable_id','bookmarkable_type', 'user_id']);

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
