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

class CreateLocalizationsTable extends Migration
{
    const TABLE_NAME = 'localizations';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {

            $table->uuid('localizable_id');
            $table->string('localizable_type');

            $table->char('region_code', 2);
            $table->json('localizations');

            $table->primary(['localizable_id', 'localizable_type', 'region_code']);

            $table->index(['localizable_id', 'localizable_type']);
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
