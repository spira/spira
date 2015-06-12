<?php namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;
use Rhumsaa\Uuid\Console\Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Validators\TestValidator;

class TestController extends BaseController
{

    public static $model = 'App\Models\TestEntity';

    /**
     * Validation service.
     *
     * @var App\Services\Validation\TestValidator
     */
    protected $validator;

    /**
     * Assign dependencies.
     *
     * @return void
     */
    public function __construct(TestValidator $validator)
    {
        $this->validator = $validator;

        parent::__construct();
    }

    public function postOne(\Illuminate\Http\Request $request)
    {
        if (!$this->validator->with($request->all())->passes()) {
            return 'failed';
        }
        return 'passed';
    }

    public function email(Request $request){

        $subject = $request->input('subject');

        $responseCode = Mail::send('emails.welcome', [], function($message) use ($subject)
        {
            $message->to('foo@example.com', 'John Smith')->subject($subject);
        });

        $message = [
            'message' => "testing email",
            'response_code' => $responseCode,
        ];

        return response()->json($message);

    }

    public function queue(){

        Queue::push(function($job){

            User::fakeUser();

            $job->delete();
        });

        $message = [
            'message' => "testing queue",
        ];

        return response()->json($message);

    }

    public function internalException(){

        throw new \RuntimeException("Something went wrong");

    }

    public function fatalError(){

        call_to_non_existent_function();

    }

    public function addToCache(Request $request, $key){

        $requestKey = $request->input('key');
        $requestValue = $request->input('value');

        if ($key != $requestKey){
            throw new BadRequestHttpException("Route parameter must match key value");
        }

        Cache::put($requestKey, $requestValue, Carbon::now()->addMinutes(1));

        return response(null, 204);

    }

    public function getFromCache($key){

        if (!Cache::has($key)) {
            throw new NotFoundHttpException("Cache does not have key `$key` stored");
        }

        $response = [
            'key' => $key,
            'value' => Cache::get($key),
        ];

        return response()->json($response, 200);

    }

    /**
     * @param Request $request
     */
    public function postLogs(Request $request){


        foreach ($request->json() as $log){

            $logType = $log['type'];
            $logMessage = $log['message'];


            $logSuccess = Log::$logType($logMessage);

            if (!$logSuccess){
                throw new \RuntimeException('Could not post log');
            }

        }

        return response(null, 204);

    }


}
