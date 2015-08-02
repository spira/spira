<?php

namespace App\Providers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Services\SpiraValidator;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Paginator\RangeRequest;

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
        $this->app->bind(TransformerInterface::class, EloquentModelTransformer::class);
        $this->app->bind(PaginatedRequestDecoratorInterface::class, RangeRequest::class);

        Validator::resolver(function ($translator, $data, $rules, $messages) {
            return new SpiraValidator($translator, $data, $rules, $messages);
        });
    }
}
