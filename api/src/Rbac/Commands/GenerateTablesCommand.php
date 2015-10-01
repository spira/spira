<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 01.10.15
 * Time: 17:51
 */

namespace Spira\Rbac\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;

class GenerateTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:generate-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates rbac tables in the db';

    public function handle()
    {
        if (! \Schema::hasTable('auth_item')){
            \Schema::create('auth_item', function (Blueprint $table) {
                $table->string('name', 128)->primary();
                $table->integer('type')->index();
                $table->text('description')->nullable();
                $table->string('rule_name', 128)->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();
            });
        }


        if (! \Schema::hasTable('auth_item_child')) {

            \Schema::create('auth_item_child', function (Blueprint $table) {
                $table->string('parent', 128);
                $table->string('child', 128);
                $table->primary(['parent', 'child']);
                $table->foreign('parent')
                    ->references('name')->on('auth_item')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('child')
                    ->references('name')->on('auth_item')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        if (! \Schema::hasTable('auth_assignment')) {
            \Schema::create('auth_assignment', function (Blueprint $table) {
                $table->string('item_name', 128);
                $table->string('user_id', 64);
                $table->dateTime('created_at');
                $table->primary(['item_name', 'user_id']);

                $table->foreign('item_name')
                    ->references('name')->on('auth_item')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }
}