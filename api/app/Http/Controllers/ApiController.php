<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 0:50
 */

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller;
use Spira\Repository\Model\BaseModel;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Response\ApiResponse;

abstract class ApiController extends Controller
{
    protected $paginatorDefaultLimit = 10;
    protected $paginatorMaxLimit = 50;
    protected $validateRequestRule = 'uuid';

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @var BaseModel
     */
    protected $model;

    public function __construct(BaseModel $model, TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
        $this->model = $model;
    }

    /**
     * @return ApiResponse
     */
    public function getResponse()
    {
        return new ApiResponse();
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }
}
