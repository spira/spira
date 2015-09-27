<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


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
            $table->string('name', 64)->primary();
            $table->integer('type')->index();
            $table->text('description')->nullable();
            $table->string('rule_name', 64)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('auth_item_child', function (Blueprint $table) {
            $table->string('parent', 64);
            $table->string('child', 64);
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
            $table->string('item_name', 64);
            $table->string('user_id', 64);
            $table->dateTime('created_at');
            $table->primary(['item_name', 'user_id']);

            $table->foreign('item_name')
                ->references('name')->on('auth_item')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
