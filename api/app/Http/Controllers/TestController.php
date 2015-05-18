<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;

class TestController extends BaseController
{

    public static $model = false;


    public function testEmail(){

        Mail::send('emails.welcome', ['key' => 'value'], function($message)
        {
            $message->to('foo@example.com', 'John Smith')->subject('Welcome!');
        });

        $message = [
            'message' => "testing email"
        ];

        return response()->json($message);

    }


}
