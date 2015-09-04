<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(\App\Models\ArticleMeta::getTableName(), function (Blueprint $table) {
                $table->uuid('article_id');
                $table->string('meta_name', 255);
                $table->string('meta_content', 255)->nullable();
                $table->string('meta_property', 255)->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->primary(['article_id','meta_name']);

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
        DB::statement(sprintf('DROP TABLE %s CASCADE', \App\Models\ArticleMeta::getTableName()));
    }
}
