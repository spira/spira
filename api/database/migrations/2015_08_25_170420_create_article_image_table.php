<?php

use App\Models\ArticleImage;
use Illuminate\Support\Facades\Schema;
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
        Schema::create(ArticleImage::getTableName(), function (Blueprint $table) {
            $table->uuid('article_image_id')->primary();
            $table->uuid('image_id');
            $table->uuid('article_id');
            $table->enum('image_type', ['primary','thumbnail','carousel'])->nullable();
            $table->string('alt', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->smallInteger('position', false, true)->nullable();

            $table->unique(['image_id','article_id']);

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
        Schema::drop(ArticleImage::getTableName());
    }
}
