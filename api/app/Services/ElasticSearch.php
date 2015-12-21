<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use App\Models\Article;
use Elasticsearch\Client;
use Spira\Core\Model\Model\IndexedModel;

class ElasticSearch
{
    private static $indexedModels = [
        User::class,
        Article::class,
        Tag::class,
    ];

    /** @var  \Cloudinary */
    protected $elasticClient;

    public function __construct(Client $elasticClient)
    {
        $this->elasticClient = $elasticClient;
    }

    /**
     * Get ElasticSearch Client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->elasticClient;
    }

    /**
     * @return array
     */
    public static function getConfig()
    {
        if (config()->has('elasticquent.config')) {
            return config()->get('elasticquent.config');
        }
        return [];
    }

    public static function getDefaultIndexName()
    {
        if (config()->has('elasticquent.default_index')) {
            return config()->get('elasticquent.default_index');
        }

        // Otherwise we will just go with 'default'
        return 'default';
    }

    /**
     * Create index
     * @param IndexedModel|null $model
     */
    public function createIndex(IndexedModel $model = null)
    {

        $indexName = self::getDefaultIndexName();

        if ($model) {
            $indexName = $model->getIndexName();
        }

        $settings = $this->getIndexConfig($indexName);

        $this->getClient()->indices()->create($settings);
    }

    /**
     * @param IndexedModel|null $model
     * @return array
     */
    public function deleteIndex(IndexedModel $model = null)
    {
        $indexName = self::getDefaultIndexName();

        if ($model) {
            $indexName = $model->getIndexName();
        }

        $config = [
            'index' => $indexName,
        ];

        return $this->getClient()->indices()->delete($config);
    }

    /**
     * @param IndexedModel|null $model
     * @return array
     */
    public function indexExists(IndexedModel $model = null)
    {
        $indexName = self::getDefaultIndexName();

        if ($model) {
            $indexName = $model->getIndexName();
        }

        $config = [
            'index' => $indexName,
        ];

        return $this->getClient()->indices()->exists($config);
    }

    /**
     * @param $indexName
     * @return array
     */
    protected function getIndexConfig($indexName)
    {
        $settings = [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'filter' => [
                            'autocomplete_filter' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 1,
                                'max_gram' => 20,
                            ],
                        ],
                        'analyzer' => [
                            'autocomplete' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
                                'filter' => [
                                    'lowercase',
                                    'autocomplete_filter',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        return $settings;
    }

    public function getIndexedModelClasses()
    {
        return self::$indexedModels;
    }

}
