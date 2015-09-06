<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

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
            $table->uuid('user_id');
            $table->string('provider', 16);
            $table->string('token');
            $table->timestamps();

            $table->primary(['user_id', 'provider']);
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
