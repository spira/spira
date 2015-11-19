<?php

use App\Models\Meta;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Meta::getTableName(), function (Blueprint $table) {

            $table->uuid(Meta::getPrimaryKey())->primary();

            $table->uuid('metaable_id');
            $table->enum('metaable_type', Meta::$metaableModels);

            $table->string('meta_name', 255);
            $table->string('meta_content', 255)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->unique(['metaable_id', 'meta_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(Meta::getTableName());
    }
}
