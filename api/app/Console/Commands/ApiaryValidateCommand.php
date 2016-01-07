<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Console\Commands;

use App\Http\Controllers\ApiaryController;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ApiaryValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apiary:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate Apiary Documentation';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apiaryController = new ApiaryController();

        $apib = $apiaryController->getDocumentationApib('/');

        $fs = new Filesystem();

        $fileLocation = storage_path().'/app/apiary.apib';

        $fs->put($fileLocation, $apib);

        $validator = base_path().'/node_modules/.bin/api-blueprint-validator';

        exec("$validator $fileLocation --fail-on-warning", $output, $exitCode);

        if ($exitCode == 0) {
            $this->info('Apiary Documentation validation passed');
        } else {
            $this->error('Apiary Documentation validation failed');
        }

        return $exitCode;
    }
}
