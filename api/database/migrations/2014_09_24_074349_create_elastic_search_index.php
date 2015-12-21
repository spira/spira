<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Services\ElasticSearch;
use Illuminate\Database\Migrations\Migration;

class CreateElasticSearchIndex extends Migration
{

    /** @var  ElasticSearch */
    protected $elasticSearch;

    public function __construct()
    {
        $this->elasticSearch = App::make(ElasticSearch::class);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->elasticSearch->indexExists()) {
            $this->elasticSearch->reindexAll(false);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->elasticSearch->indexExists()){
            $this->elasticSearch->deleteIndex();
        }
    }
}
