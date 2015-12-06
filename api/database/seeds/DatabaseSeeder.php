<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('ImageSeeder');
        $this->call('UserStorySeeder');

        if (env('APP_ENV') === 'demo') {
            $this->call('TestEntitySeeder');
            $this->call('ArticleSeeder');
        }
    }
}
