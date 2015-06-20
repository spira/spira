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

use App\Repositories\TestRepository;
use App\Http\Validators\TestValidator;

class TestController extends BaseController
{
    /**
     * Assign dependencies.
     *
     * @param  TestValidator $validator
     * @param  TestRepository  $repository
     * @return void
     */
    public function __construct(TestValidator $validator, TestRepository $repository)
    {
        $this->validator = $validator;
        $this->repository = $repository;
    }

    /**
     * Post a new entity.
     *
     * @param  Request $request
     * @return mixed
     */
    public function postOne(Request $request)
    {
        if (!$this->validator->with($request->all())->passes()) {
            return $this->validator->errors();
        }

        return parent::postOne($request);
    }

    public function email(Request $request){

        $subject = $request->input('subject');

        $responseCode = Mail::send('emails.welcome', [], function($message) use ($subject)
        {
            $message->to('foo@example.com', 'John Smith')->subject($subject);
        });

        $message = [
            'message' => "testing email",
            'responseCode' => $responseCode,
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
