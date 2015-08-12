<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 0:50
 */

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Response\ApiResponse;

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
}
