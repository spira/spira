<?php

use App\Models\Article;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Article::getTableName(), function (Blueprint $table) {
                $table->uuid('article_id')->primary();
                $table->string('title', 255);
                $table->enum('status', Article::$statuses)->default(Article::STATUS_DRAFT);
                $table->text('content');
                $table->text('excerpt')->nullable();
                $table->string('primary_image')->nullable();
                $table->string('permalink')->index()->nullable();
                $table->uuid('author_id')->index()->nullable();
                $table->dateTime('first_published')->nullable();

                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();
                $table->foreign('author_id')
                    ->references('user_id')->on(\App\Models\User::getTableName())
                    ->onDelete('set null');
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
        Schema::drop(Article::getTableName());
    }
}
