<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spira\Model\Model\BaseModel;
use Laravel\Lumen\Routing\Controller;
use Spira\Model\Collection\Collection;
use App\Exceptions\BadRequestException;
use Spira\Responder\Response\ApiResponse;
use Spira\Responder\Contract\TransformerInterface;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

abstract class ApiController extends Controller
{
    protected $paginatorDefaultLimit = 10;
    protected $paginatorMaxLimit = 50;

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
        $this->middleware('transaction');
    }

    /**
     * @return ApiResponse
     */
    public function getResponse()
    {
        return new ApiResponse();
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param Collection|BaseModel $modelOrCollection
     * @param Request $request
     * @return mixed
     */
    protected function getWithNested($modelOrCollection, Request $request)
    {
        if ((! $modelOrCollection instanceof BaseModel) && (! $modelOrCollection instanceof EloquentCollection)) {
            throw new \InvalidArgumentException(sprintf('Model must be instance of %s or %s. %s given.', BaseModel::class, EloquentCollection::class, get_class($modelOrCollection)));
        }

        $nested = $request->headers->get('With-Nested');
        if (! $nested) {
            return $modelOrCollection;
        }

        $requestedRelations = explode(', ', $nested);

        try {
            $modelOrCollection->load($requestedRelations);
        } catch (\BadMethodCallException $e) {
            throw new BadRequestException(sprintf('Invalid `With-Nested` request - one or more of the following relationships do not exist for %s:[%s]', get_class($modelOrCollection), $nested), null, $e);
        }

        return $modelOrCollection;
    }
}
