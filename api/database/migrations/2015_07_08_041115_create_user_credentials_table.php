<?php

use Bosnadev\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCredentialsTable extends Migration
{
    const MODEL = 'App\Models\UserCredential';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelClass = static::MODEL;
        Schema::create($modelClass::getTableName(), function (Blueprint $table) use ($modelClass) {
            $table->uuid('user_credential_id');

            $table->uuid('user_id')->unique();
            $table->char('password', 60);

            $table->primary('user_credential_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $modelClass = static::MODEL;
        Schema::drop($modelClass::getTableName());
    }
}
