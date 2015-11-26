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

class CreatePostsPermalinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(PostPermalink::getTableName(), function (Blueprint $table) {
            $table->string('permalink', 255)->primary();
            $table->uuid('post_id')->index()->nullable();

            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->foreign('post_id')
                ->references('post_id')->on(AbstractPost::getTableName())
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
        DB::statement(sprintf('DROP TABLE %s CASCADE', PostPermalink::getTableName()));
    }
}
