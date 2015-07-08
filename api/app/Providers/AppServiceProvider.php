<?php namespace App\Providers;

use App\Http\Transformers\CollectionTransformerInterface;
use App\Http\Transformers\IlluminateModelTransformer;
use App\Http\Transformers\ItemTransformerInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\ServiceProvider;

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
        $this->app->bind(CollectionTransformerInterface::class, IlluminateModelTransformer::class);
        $this->app->bind(ItemTransformerInterface::class, IlluminateModelTransformer::class);

    }
}
