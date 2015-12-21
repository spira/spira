<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\AbstractPost;
use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AbstractPost::getTableName(), function (Blueprint $table) {
            $table->uuid('post_id')->primary();
            $table->string('title', 255);
            $table->enum('status', AbstractPost::$statuses)->default(AbstractPost::STATUS_DRAFT);
            $table->text('excerpt')->nullable();
            $table->uuid('thumbnail_image_id')->nullable();
            $table->string('permalink')->index()->nullable();
            $table->uuid('author_id')->index()->nullable();
            $table->string('author_override', 255)->nullable()->default(null);
            $table->string('author_website')->nullable()->default(null);
            $table->boolean('show_author_promo')->default(false);
            $table->dateTime('first_published')->nullable();
            $table->json('sections_display')->nullable();
            $table->boolean('users_can_comment')->default(false);
            $table->boolean('public_access')->default(false);
            $table->enum('post_type', AbstractPost::$postTypes)->index();

            $table->timestamps();

            $table->foreign('author_id')
                ->references('user_id')->on(User::getTableName())
                ->onDelete('set null');

            $table->foreign('thumbnail_image_id')
                ->references(Image::getPrimaryKey())->on(Image::getTableName())
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(AbstractPost::getTableName());
    }
}
