<?php namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;

class TestController extends BaseController
{

    public static $model = false;


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


}
