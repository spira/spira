<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesPermalinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(\App\Models\ArticlePermalink::getTableName(), function (Blueprint $table) {
                $table->string('permalink', 255)->primary();
                $table->uuid('article_id')->index()->nullable();

                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->foreign('article_id')
                    ->references('article_id')->on(\App\Models\Article::getTableName())
                    ->onDelete('cascade');
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
        DB::statement(sprintf('DROP TABLE %s CASCADE', \App\Models\ArticlePermalink::getTableName()));
    }
}
