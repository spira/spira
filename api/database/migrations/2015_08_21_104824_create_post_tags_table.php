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

class CreatePostTagsTable extends Migration
{
    const TABLE_NAME = 'post_tag';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('tag_id');
            $table->uuid('post_id');

            $table->uuid('tag_group_id');
            $table->uuid('tag_group_parent_id');

            $table->primary(['tag_id', 'post_id']);

            $table->foreign('post_id')
                ->references('post_id')->on(\App\Models\AbstractPost::getTableName())
                ->onDelete('cascade');

            $table->foreign('tag_id')
                ->references('tag_id')->on(Tag::getTableName())
                ->onDelete('cascade');

            $table->foreign('tag_group_id')
                ->references('tag_id')->on(Tag::getTableName())
                ->onDelete('cascade');

            $table->foreign('tag_group_parent_id')
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
