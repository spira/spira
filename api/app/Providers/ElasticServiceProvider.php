<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Providers;

use App\Console\Commands\SearchBuildIndexCommand;
use App\Services\ElasticSearch;
use Elasticsearch\Client;
use Illuminate\Support\ServiceProvider;

class ElasticServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerClient();
        $this->registerService();
        $this->commands([SearchBuildIndexCommand::class]);
    }

    protected function registerClient()
    {
        $this->app->singleton(Client::class, function ($app) {
            return new Client($this->getClientConfig());
        });
    }

    protected function registerService()
    {
        $this->app->singleton(ElasticSearch::class, function ($app) {
            return new ElasticSearch($app[Client::class], $this->getDefaultIndexNameFromConfig());
        });
    }

    protected function getDefaultIndexNameFromConfig()
    {
        if (config()->has('elasticquent.default_index')) {
            return config()->get('elasticquent.default_index');
        }

        return 'default';
    }

    /**
     * @return array
     */
    protected function getClientConfig()
    {
        if (config()->has('elasticquent.config')) {
            return config()->get('elasticquent.config');
        }

        return [];
    }
}
