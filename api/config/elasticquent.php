<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Elasticsearch Client Configuration
    |--------------------------------------------------------------------------
    |
    | This array will be passed to the Elasticsearch client.
    | See configuration options here:
    |
    | http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html
    */

    'config' => [
        'hosts'     => [sprintf('%s:%s', env('ELASTICSEARCH_HOST'), env('ELASTICSEARCH_PORT_NUMBER'))],
        'logging'   => true,
        'logPath'   => storage_path() . '/logs/elasticsearch.log',
        'logLevel'  => Monolog\Logger::WARNING,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elastiquent will use for all
    | Elastiquent models.
    */

    'default_index' => 'my_custom_index_name',

];