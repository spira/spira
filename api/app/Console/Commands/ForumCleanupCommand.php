<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ForumCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forum:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init of Vanilla Forum & database refresh';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dir = realpath(base_path() . '/../forum');
        exec('composer run-script post-install-cmd --working-dir "' . $dir . '"', $output, $exitCode);

        return $exitCode;
    }
}
