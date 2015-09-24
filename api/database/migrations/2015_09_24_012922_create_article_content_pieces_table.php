<?php

use App\Models\Article;
use App\Models\ArticleContentPiece;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateArticleContentPiecesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(ArticleContentPiece::getTableName(), function (Blueprint $table) {

            $pk = ArticleContentPiece::getPrimaryKey();
            $articleFk = Article::getPrimaryKey();

            $table->uuid($pk);
            $table->uuid($articleFk);

            $table->primary([$pk,$articleFk]);

            $table->foreign($articleFk)
                ->references($articleFk)->on(Article::getTableName())
                ->onDelete('cascade');

            $table->json('content');
            $table->enum('type', ArticleContentPiece::getContentTypes());
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
        Schema::drop(ArticleContentPiece::getTableName());
    }
}
