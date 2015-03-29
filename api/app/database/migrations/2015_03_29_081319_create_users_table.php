<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {


    const TABLE_NAME = 'users';
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create(static::TABLE_NAME, function(Blueprint $table)
            {
                $table->engine = 'InnoDB';

                $table->char('user_id', 36);
                $table->string('email', 255)->unique();
                $table->char('password', 60);
                $table->string('first_name', 45)->nullable();
                $table->string('last_name', 45)->nullable();
                $table->string('phone', 45)->nullable();
                $table->string('mobile', 45)->nullable();

                $table->dateTime('created_at');
                $table->dateTime('updated_at');

                $table->primary('user_id');
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
        Schema::drop(static::TABLE_NAME);
    }

}
