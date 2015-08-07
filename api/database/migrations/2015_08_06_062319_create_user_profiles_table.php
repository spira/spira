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
            $table->date('dob')->nullable();
            $table->enum('gender', array('M', 'F'))->nullable();
            $table->string('about', $modelClass::ABOUT_LENGTH)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('twitter', 45)->nullable();
            $table->string('pinterest', 100)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('website', 100)->nullable();

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
