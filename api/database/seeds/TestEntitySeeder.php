<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Symfony\Component\Console\Helper\ProgressBar;

class TestEntitySeeder extends BaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->comment('Seeding Test Entities');

        /** @var ProgressBar $progressBar */
        $progressBar = $this->command->getOutput()->createProgressBar(20);

        factory(App\Models\TestEntity::class, 20)->create();

        $progressBar->finish();
        $this->command->line('');
    }
}
