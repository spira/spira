<?php

use Bosnadev\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    const TABLE_NAME = 'users';
    const MODEL = 'App\Models\User';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelClass = static::MODEL;

        Schema::create($modelClass::getTableName(), function (Blueprint $table) use ($modelClass) {
                $table->uuid('user_id');
                $table->string('email', 255)->unique();
                $table->string('first_name', 45)->nullable();
                $table->string('last_name', 45)->nullable();
                $table->string('phone', 45)->nullable();
                $table->string('mobile', 45)->nullable();
                $table->string('country', 2)->nullable();
                $table->string('timezone_identifier', 40)->nullable();
                $table->enum('user_type', $modelClass::$userTypes);

                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

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
