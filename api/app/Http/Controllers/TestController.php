<?php namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeds\UserStorySeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class TestController extends BaseController
{

    public static $model = false;


    public function email(){

        $responseCode = Mail::send('emails.welcome', [], function($message)
        {
            $message->to('foo@example.com', 'John Smith')->subject('Welcome!');
        });

        $message = [
            'message' => "testing email",
            'response_code' => $responseCode,
        ];

        return response()->json($message);

    }

    public function queue(){

        $userSeeder = new UserStorySeeder();

        Queue::push(function($job) use ($userSeeder){

            $userSeeder->createUser();


            $job->delete();
        });

        $message = [
            'message' => "testing queue",
        ];

        return response()->json($message);

    }


}
