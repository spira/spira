<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Core\Providers;

use Illuminate\Validation\Factory;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\ServiceProvider;
use Spira\Core\Responder\Contract\TransformerInterface;
use Spira\Core\Responder\Paginator\PaginatedRequestDecoratorInterface;
use Spira\Core\Responder\Paginator\RangeRequest;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;
use Spira\Core\Validation\SpiraValidator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $spiraMessages = [
            'uuid'                 => 'The :attribute must be an UUID string.',
            'not_required_if'      => 'The :attribute must be null',
            'decimal'              => 'The :attribute must be a decimal.',
            'not_found'            => 'The selected :attribute is invalid.',
            'country'              => 'The :attribute must be a valid country code.',
            'string'               => 'The :attribute must be text',
            'decoded_json'         => 'The :attribute must be an object or an array',
            'alpha_dash_space'     => 'The :attribute may only contain letters, numbers, dashes and spaces.',
            'supported_region'     => 'The :attribute must be a supported region. Supported region codes are ('.implode(', ', array_pluck(config('regions.supported'), 'code')).')',
        ];

        $this->app->extend('validator', function (Factory $validator)  use ($spiraMessages){
            $validator->resolver(function ($translator, $data, $rules, $messages, $customAttributes) use ($spiraMessages) {
                return new SpiraValidator($translator, $data, $rules, array_merge($messages, $spiraMessages), $customAttributes);
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
