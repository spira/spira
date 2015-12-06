<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Spira\Core\Controllers\ApiController;
use Spira\Core\Model\Datasets\Countries;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;
use Symfony\Component\HttpFoundation\Response;

class CountriesController extends ApiController
{
    /**
     * Assign dependencies.
     *
     * @param  Countries $countries
     * @param  EloquentModelTransformer $transformer
     */
    public function __construct(Countries $countries, EloquentModelTransformer $transformer)
    {
        $this->countries = $countries;
        parent::__construct($transformer);
    }

    /**
     * Get all entities.
     *
     * @return Response
     */
    public function getAll()
    {
        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($this->countries->all());
    }
}
