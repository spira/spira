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

class CreateTagTagTable extends Migration
{
    const TABLE_NAME = 'tag_tag';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('tag_id');
            $table->uuid('parent_tag_id');
            $table->boolean('required')->default(false);
            $table->boolean('linked_tags_must_exist')->default(false);
            $table->boolean('linked_tags_must_be_children')->default(false);
            $table->tinyInteger('linked_tags_limit')->nullable()->default(null);

            $table->primary(['tag_id','parent_tag_id']);

            $table->foreign('tag_id')
                ->references('tag_id')->on(Tag::getTableName())
                ->onDelete('cascade');

            $table->foreign('parent_tag_id')
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
