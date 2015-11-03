<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Services\SpiraValidator;
use Illuminate\Validation\Factory;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\ServiceProvider;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Responder\Paginator\RangeRequest;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->extend('validator', function(Factory $validator){
            $validator->resolver(function ($translator, $data, $rules, $messages, $customAttributes) {
                return new SpiraValidator($translator, $data, $rules, $messages, $customAttributes);
            });
            return $validator;
        });
    }

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
    }
}
