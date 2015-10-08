<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spira\Rbac\Item\Item;

class CreateRbacTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_item', function (Blueprint $table) {
            $table->string('name', 128)->primary();
            $table->enum('type', [Item::TYPE_ROLE, Item::TYPE_PERMISSION])->index();
            $table->text('description')->nullable();
            $table->string('rule_name', 128)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('auth_item_child', function (Blueprint $table) {
            $table->string('parent', 128);
            $table->string('child', 128);
            $table->primary(['parent', 'child']);
            $table->foreign('parent')
                ->references('name')->on('auth_item')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('child')
                ->references('name')->on('auth_item')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::create('auth_assignment', function (Blueprint $table) {
            $table->string('item_name', 128);
            $table->string('user_id', 64);
            $table->dateTime('created_at');
            $table->primary(['item_name', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('auth_assignment');
        Schema::drop('auth_item_child');
        Schema::drop('auth_item');
    }
}
