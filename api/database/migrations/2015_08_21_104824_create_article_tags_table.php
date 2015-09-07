<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Tag;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTagsTable extends Migration
{
    const TABLE_NAME = 'tag_article';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('tag_id');
            $table->uuid('article_id');

            $table->primary(['tag_id','article_id']);

            $table->foreign('article_id')
                ->references('article_id')->on(\App\Models\Article::getTableName())
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('tag_id')->on(Tag::getTableName())
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
        Schema::drop(static::TABLE_NAME);
    }
}
