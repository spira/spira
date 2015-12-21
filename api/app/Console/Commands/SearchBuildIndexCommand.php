<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Console\Commands;

use App\Services\ElasticSearch;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Spira\Core\Model\Model\IndexedModel;

class SearchBuildIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:build-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(re)Build search index';


    /**
     * ElasticSearch Service.
     *
     * @var ElasticSearch
     */
    protected $elasticSearch;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $file
     */
    public function __construct(ElasticSearch $elasticSearch)
    {
        parent::__construct();

        $this->elasticSearch = $elasticSearch;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if ($this->elasticSearch->indexExists()){
            $this->elasticSearch->deleteIndex();
        }

        $this->elasticSearch->createIndex();

        $indexedModelClasses = $this->elasticSearch->getIndexedModelClasses();

        foreach ($indexedModelClasses as $className){

            /** @var $className IndexedModel */
            $className::putMapping();
            $className::addAllToIndex();
        }

        return 0;
    }
}
