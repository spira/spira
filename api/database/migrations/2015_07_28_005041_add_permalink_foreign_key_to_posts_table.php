<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\AbstractPost;
use App\Models\PostPermalink;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPermalinkForeignKeyToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(AbstractPost::getTableName(), function (Blueprint $table) {
            $table->foreign('permalink', 'post_permalink_foreign')
                ->references('permalink')->on(PostPermalink::getTableName());
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(AbstractPost::getTableName(), function (Blueprint $table) {
            $table->dropForeign('post_permalink_foreign');
        });
    }
}
