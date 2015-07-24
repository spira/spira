<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Laravel\Lumen\Routing\Controller;
use Faker;

class ApiaryController extends Controller
{
    public function index()
    {
        return view('documentation.layouts.master', [
            'apibUrl' => '/documentation.apib',
        ]);
    }

    public function getApiaryDocumentation(Request $request)
    {
        return $this->getDocumentationApib($request->root());
    }

    public function getDocumentationApib($apiUrl)
    {
        View::addExtension('blade.apib', 'blade'); //allow sections to be defined as .blade.apib for correct syntax highlighting

        return view('documentation.apiary', [
            'apiUrl' => $apiUrl,
            'faker'  => Faker\Factory::create(),
        ]);
    }
}
