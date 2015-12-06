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
use Illuminate\Filesystem\Filesystem;

class GenerateKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:generate-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate RSA key pair for the application.';

    /**
     * Filesystem.
     *
     * @var Filesystem
     */
    protected $file;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $file
     */
    public function __construct(Filesystem $file)
    {
        parent::__construct();

        $this->file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rsa = new \Crypt_RSA();
        $keys = $rsa->createKey();

        if (! $this->file->exists(storage_path('app/keys'))) {
            $this->file->makeDirectory(storage_path('app/keys'));
        }

        $this->file->put(storage_path('app/keys/private.pem'), $keys['privatekey']);
        $this->file->put(storage_path('app/keys/public.pem'), $keys['publickey']);

        return 0;
    }
}
