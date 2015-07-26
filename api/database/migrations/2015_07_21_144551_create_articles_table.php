<?php

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
        Schema::create(\App\Models\Article::getTableName(), function (Blueprint $table) {
                $table->uuid('article_id')->primary();
                $table->string('title', 255);
                $table->text('content');
                $table->string('permalink',255)->index()->nullable();
                $table->dateTime('first_published')->nullable();

                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();
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
        Schema::drop(\App\Models\Article::getTableName());
    }
}
