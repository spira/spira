<?php

use App\Models\Image;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Image::getTableName(), function (Blueprint $table) {
                $table->uuid('image_id')->primary();
                $table->string('public_id', 255);
                $table->integer('version');
                $table->string('format',4);
                $table->string('folder',10)->nullable();
                $table->string('alt',255)->nullable();
                $table->string('title',255)->nullable();

                $table->unique(['public_id','version']);

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
        Schema::drop(Image::getTableName());
    }
}
