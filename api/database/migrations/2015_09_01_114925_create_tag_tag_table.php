<?php

use App\Models\Tag;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_tag', function (Blueprint $table) {
            $table->uuid('tag_id');
            $table->uuid('fk_tag_id');

            $table->primary(['tag_id','fk_tag_id']);

            $table->foreign('tag_id')
                ->references('tag_id')->on(Tag::getTableName())
                ->onDelete('cascade');

            $table->foreign('fk_tag_id')
                ->references('fk_tag_id')->on(Tag::getTableName())
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
        Schema::drop('tag_article');
    }
}
