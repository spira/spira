<?php namespace App\Http\Controllers;

use App\Http\Transformers\BaseTransformer;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseController extends Controller
{
    public static $model;

    /**
     * Transformer for the response arrays.
     *
     * @var App\Services\Transformer
     */
    protected $transformer;

    /**
     * Assign dependencies.
     *
     * @return void
     */
    public function __construct()
    {
        $this->transformer = \App::make('App\Services\Transformer');
    }

    public function getAll()
    {
        $model = static::$model;

        $entities = $model::all();

        return $this->transformer->collection($entities, new BaseTransformer);
    }

    public function getOne($id)
    {
        $model = static::$model;

        $resource = $model::find($id);

        if(! $resource) {

            throw new NotFoundHttpException($model . ' not found');
        }

        return $this->transformer->item($resource, new BaseTransformer);
    }

    public function postOne(\Illuminate\Http\Request $request)
    {
        return $request->all();
    }

    public static function renderException($request, \Exception $e, $debug = false){

        $message = $e->getMessage();
        if (!$message){
            $message = 'An error occurred';
        }

        $debugData = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ];

        $response = [
            'message' => $message,
        ];

        $statusCode = 500;

        if ($e instanceof HttpExceptionInterface){
            $statusCode = $e->getStatusCode();
        }

        if ($debug){
            $response['debug'] = $debugData;
        }

        return response()->json($response, $statusCode);
    }

}
