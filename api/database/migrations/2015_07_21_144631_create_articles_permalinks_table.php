<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;
use App\Models\ArticlePermalink;
use Illuminate\Support\Facades\Schema;
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
        Schema::create(ArticlePermalink::getTableName(), function (Blueprint $table) {
            $table->string('permalink', 255)->primary();
            $table->uuid('article_id')->index()->nullable();

            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->foreign('article_id')
                ->references('article_id')->on(Article::getTableName())
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
        DB::statement(sprintf('DROP TABLE %s CASCADE', ArticlePermalink::getTableName()));
    }
}
