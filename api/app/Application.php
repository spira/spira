<?php namespace App;

use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Application;
use Monolog\Handler\SyslogUdpHandler;

class SpiraApplication extends Application
{

    /**
     * Get the Monolog handler for the application.
     *
     * @return \Monolog\Handler\AbstractHandler
     */
    protected function getMonologHandler()
    {

        if (env('LOG_UDP_HOST')){
            return new SyslogUdpHandler(env('LOG_UDP_HOST'), env('LOG_UDP_PORT'));
        }

        return parent::getMonologHandler();
    }


    /**
     * Get the HTML for the API documentation screen.
     *
     * @return string
     */
    public function apiaryDocumentation()
    {

        $contents = Storage::disk('global')->get('apiary/spira.tpl.apib');

        $encoded = json_encode($contents);

        $html = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Spira - API Documentation</title>
</head>
<body>
  <script src="https://api.apiary.io/seeds/embed.js"></script>
  <script>
    var embed = new Apiary.Embed({
      apiBlueprint: $encoded
    });
  </script>
</body>
</html>
EOT;

        return $html;

    }


}
