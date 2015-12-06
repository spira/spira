<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Image;
use Illuminate\Support\Facades\Schema;
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
            $table->integer('version');
            $table->string('format', 4);
            $table->string('folder', 10)->nullable();
            $table->string('alt', 255);
            $table->string('title', 255)->nullable();

            $table->unique(['image_id', 'version']);
        });
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
