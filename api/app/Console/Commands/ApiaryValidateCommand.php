<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $webserverIp = getenv('WEBSERVER_HOST');
        $webserverPort = getenv('WEBSERVER_PORT');

        $url = "http://$webserverIp:$webserverPort/documentation.apib";

        $output = null;
        $return = null;

        $fileLocation = storage_path().'/app/apiary.apib';
        $validator = base_path().'/node_modules/.bin/api-blueprint-validator';

        exec("wget $url -O $fileLocation && $validator $fileLocation", $output, $exitCode);

        return $exitCode;
    }
}
