<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 0:50
 */

namespace App\Http\Controllers;

use App\Repositories\BaseRepository;
use Laravel\Lumen\Routing\Controller;
use Spira\Responder\Contract\TransformerInterface;
use Spira\Responder\Response\ApiResponse;


abstract class ApiController extends Controller
{

    protected $paginatorDefaultLimit = 10;
    protected $paginatorMaxLimit = 50;
    protected $validateRequest = true;
    protected $validateRequestRule = 'uuid';

    /**
     * Model Repository.
     *
     * @var BaseRepository
     */
    protected $repository;

    /**
     * @var TransformerInterface
     */
    protected $transformer;

    public function __construct(BaseRepository $repository, TransformerInterface $transformer)
    {
        $this->repository = $repository;
        $this->transformer = $transformer;
    }

    /**
     * @return ApiResponse
     */
    public function getResponse()
    {
        return new ApiResponse();
    }

    /**
     * @return BaseRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return mixed
     */
    public function getKeyName()
    {
        return $this->getRepository()->getKeyName();
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }
}