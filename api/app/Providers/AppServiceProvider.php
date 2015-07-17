<?php

namespace App\Providers;

use App\Http\Transformers\IlluminateModelTransformer;
use App\Services\SpiraValidator;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spira\Responder\Contract\ApiResponderInterface;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Responder\ApiResponder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('League\Fractal\Serializer\SerializerAbstract', 'League\Fractal\Serializer\ArraySerializer');
        $this->app->bind(ConnectionResolverInterface::class, 'db');
        $this->app->bind(TransformerInterface::class, IlluminateModelTransformer::class);
        $this->app->bind(ApiResponderInterface::class, ApiResponder::class);

        Validator::resolver(function($translator, $data, $rules, $messages)
        {
            return new SpiraValidator($translator, $data, $rules, $messages);
        });
    }
}
