<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\ArticleMeta;
use Illuminate\Support\Facades\Schema;
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
        Schema::create(ArticleMeta::getTableName(), function (Blueprint $table) {
            $table->uuid('article_id');
            $table->string('meta_name', 255);
            $table->string('meta_content', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->primary(['article_id','meta_name']);

            $table->foreign('article_id')
                ->references('article_id')->on(\App\Models\Article::getTableName())
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
        DB::statement(sprintf('DROP TABLE %s CASCADE', ArticleMeta::getTableName()));
    }
}
