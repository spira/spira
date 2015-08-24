<?php

use App\Models\Tag;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Tag::getTableName(), function (Blueprint $table) {
                $table->uuid('tag_id')->primary();
                $table->string('tag', 20)->unique();
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
        Schema::drop(Tag::getTableName());
    }
}
