<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Article;
use App\Models\ArticleSection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateArticleSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ArticleSection::getTableName(), function (Blueprint $table) {

            $pk = ArticleSection::getPrimaryKey();
            $articleFk = Article::getPrimaryKey();

            $table->uuid($pk);
            $table->uuid($articleFk);

            $table->primary([$pk,$articleFk]);

            $table->foreign($articleFk)
                ->references($articleFk)->on(Article::getTableName())
                ->onDelete('cascade');

            $table->json('content');
            $table->enum('type', ArticleSection::getContentTypes());
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(ArticleSection::getTableName());
    }
}
