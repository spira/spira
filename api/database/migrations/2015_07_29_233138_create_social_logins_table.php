<?php

use App\Models\User;
use Bosnadev\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocialLoginsTable extends Migration
{
    const MODEL = 'App\Models\SocialLogin';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelClass = static::MODEL;
        Schema::create($modelClass::getTableName(), function (Blueprint $table) use ($modelClass) {
            $table->uuid('social_login_id');

            $table->uuid('user_id');
            $table->string('provider', 16);
            $table->string('token');
            $table->timestamps();

            $table->primary('social_login_id');
            $table->foreign('user_id')
                ->references('user_id')->on(User::getTableName())
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
        $modelClass = static::MODEL;
        Schema::drop($modelClass::getTableName());
    }
}
