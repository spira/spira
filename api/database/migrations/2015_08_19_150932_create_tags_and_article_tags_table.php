<?php

use App\Models\Tag;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsAndArticleTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Tag::getTableName(), function (Blueprint $table) {
                $table->integer('tag_id',true,true);
                $table->string('tag', 255)->unique();
            }
        );

        Schema::create('tag_article', function (Blueprint $table) {
                $table->integer('tag_id');
                $table->uuid('article_id');

                $table->primary(['tag_id','article_id']);

                $table->foreign('article_id')
                    ->references('article_id')->on(\App\Models\Article::getTableName())
                    ->onDelete('cascade');

                $table->foreign('tag_id')
                    ->references('tag_id')->on(Tag::getTableName())
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
        Schema::drop('tag_article');
        Schema::drop(Tag::getTableName());
    }
}
