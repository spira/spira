<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_article', function (Blueprint $table) {
            $table->uuid('image_id');
            $table->uuid('article_id');
            $table->enum('group_type',['primary','thumbnail','carousel']);
            $table->smallInteger('position',false,true)->default(1);

            $table->primary(['image_id','article_id','group_type']);

            $table->foreign('article_id')
                ->references('article_id')->on(\App\Models\Article::getTableName())
                ->onDelete('cascade');

            $table->foreign('image_id')
                ->references('image_id')->on(\App\Models\Image::getTableName())
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
        Schema::drop('image_article');
    }

}
