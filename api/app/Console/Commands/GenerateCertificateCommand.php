<?php namespace App\Console\Commands;

use Crypt_RSA;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateCertificateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spira:generate-cert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a RSA keypair for the application.';

    /**
     * Filesystem.
     *
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $file;

    /**
     * Create a new command instance.
     *
     * @param  Illuminate\Filesystem\Filesystem  $file
     * @return void
     */
    public function __construct(Filesystem $file)
    {
        parent::__construct();

        $this->file = $file;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rsa = new Crypt_RSA();
        extract($rsa->createKey());

        if (!$this->file->exists(storage_path('app/keys'))) {
            $this->file->makeDirectory(storage_path('app/keys'));
        }

        $this->file->put(storage_path('app/keys/private.pem'), $privatekey);
        $this->file->put(storage_path('app/keys/public.pem'), $publickey);
    }
}
