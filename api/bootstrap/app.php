<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

require_once __DIR__.'/../vendor/autoload.php';

Dotenv::load(__DIR__.'/../');

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new \App\SpiraApplication(
    realpath(__DIR__.'/../')
);

$app->withFacades();
$app->withEloquent();

$app->configure('jwt');
$app->configure('cors');
$app->configure('mail');
$app->configure('hosts');
$app->configure('regions');
$app->configure('filesystems');
$app->configure('elasticquent');

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    Spira\Core\Contract\Exception\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    \Spira\Core\Middleware\TransformInputDataMiddleware::class,
    Barryvdh\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'transaction' => \Spira\Core\Middleware\TransactionMiddleware::class,
    'requireAuthorization' => \Spira\Core\Middleware\AuthorizationMiddleware::class,
    'attachUserToEntity' => \Spira\Core\Middleware\AppendUserIdToRequestBodyMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\ApplicationProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\ElasticServiceProvider::class);
$app->register(App\Providers\AccessServiceProvider::class);
$app->register(App\Providers\AuthDriverServiceProvider::class);
$app->register(\Illuminate\Mail\MailServiceProvider::class);
$app->register(\Illuminate\Redis\RedisServiceProvider::class);

$app->register(App\Extensions\Socialite\SocialiteServiceProvider::class);

$app->register(Bosnadev\Database\DatabaseServiceProvider::class);
$app->register(Barryvdh\Cors\LumenServiceProvider::class);

$app->register(Illuminate\Filesystem\FilesystemServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/../app/Http/routes.php';
});

return $app;
