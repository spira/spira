<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Laravel\Lumen\Routing\Controller;
use Faker;
use Symfony\Component\HttpFoundation\Response;

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
        $apib = $this->getDocumentationApib($request->root());

        $headers = [
            'Content-Type' => 'text/plain',
        ];

        return response($apib, Response::HTTP_OK, $headers);
    }

    public function getDocumentationApib($apiUrl)
    {
        View::addExtension('blade.apib', 'blade'); //allow sections to be defined as .blade.apib for correct syntax highlighting

        $data = [
            'apiUrl' => $apiUrl,
            'faker' => Faker\Factory::create(),
        ];

        $content = view('documentation.apiary', $data)->render();

        return $content;
    }
}
