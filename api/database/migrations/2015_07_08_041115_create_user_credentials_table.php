<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use App\Models\UserCredential;
use Illuminate\Support\Facades\Schema;
use Bosnadev\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $modelClass = UserCredential::class;
        Schema::create(UserCredential::getTableName(), function (Blueprint $table) use ($modelClass) {
            $table->uuid('user_id')->unique();
            $table->char('password', 60);

            $table->primary('user_id');

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
        Schema::drop(UserCredential::getTableName());
    }
}
