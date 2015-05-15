<?php namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseController extends Controller
{

    public static $model;

    public function getAll()
    {
        $model = static::$model;

        $entities = $model::all();

        return response()->json($entities);

//        $this->transformer = $entity.'Transformer';
//        return $this->response->collection($entities, new $this->transformer);
    }


    public function getOne($id)
    {
        $model = static::$model;

        $resource = $model::find($id);

        if(! $resource) {

            throw new NotFoundHttpException($model . ' not found');
        }

        return response()->json($resource);
    }

}
