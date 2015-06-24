<?php namespace App;

use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Application;
use Monolog\Handler\SyslogUdpHandler;
use Wpb\StringBladeCompiler\Facades\StringView;

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

        $compiledView = StringView::make(
            [
                // this actual blade template
                'template'  => $contents,
                // this is the cache file key, converted to md5
                'cache_key' => 'apiary/spira.tpl.apib',
                // timestamp for when the template was last updated, 0 is always recompile
                'updated_at' => 0
            ],
            []
        );

        $encoded = json_encode($compiledView->render());

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
