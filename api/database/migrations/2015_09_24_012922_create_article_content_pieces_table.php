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
            $table->uuid(ArticleContentPiece::getPrimaryKey());
            $table->uuid('article_id')->unique();

            $table->primary(['content_piece_id','article_id']);

            $table->foreign('article_id')
                ->references('article_id')->on(Article::getTableName())
                ->onDelete('cascade');

            $table->json('content');
            $table->enum('type', ArticleContentPiece::contentTypes);
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
        //
    }
}
