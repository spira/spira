<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\Section;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Section::getTableName(), function (Blueprint $table) {

            $pk = Section::getPrimaryKey();

            $table->uuid($pk);
            $table->uuid('sectionable_id');
            $table->enum('sectionable_type', Section::$metaableModels);

            $table->primary([$pk,'sectionable_id', 'sectionable_type']);

            $table->json('content');
            $table->enum('type', Section::getContentTypes());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(Section::getTableName());
    }
}
