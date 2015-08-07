<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserProfilesTable extends Migration
{
    const TABLE_NAME = 'user_profiles';
    const MODEL = 'App\Models\UserProfile';

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
            $table->string('phone', 45)->nullable();
            $table->string('mobile', 45)->nullable();
            $table->string('avatar_img_url')->nullable();
            $table->date('dob')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();

            $table->primary('user_id');

            $table->foreign('user_id')
                ->references('user_id')->on(\App\Models\User::getTableName())
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
