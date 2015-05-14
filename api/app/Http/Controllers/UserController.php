<?php namespace App\Http\Controllers;

use App\Http\Models\User;
use Laravel\Lumen\Routing\Controller as BaseController;

class UserController extends BaseController
{

    public function getAll(){


        $users = User::all();

        return response()->json($users);

    }

}
