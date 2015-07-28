<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermalinkForeignKeyToArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(\App\Models\Article::getTableName(), function (Blueprint $table) {
            $table->foreign('permalink')
                ->references('permalink')->on(\App\Models\ArticlePermalink::getTableName());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(\App\Models\Article::getTableName(), function (Blueprint $table) {
            $table->dropForeign('articles_permalink_foreign');
        });
    }
}
