<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Laravel\Lumen\Routing\Controller;
use Faker;
use Symfony\Component\HttpFoundation\Response;

class ApiaryController extends Controller
{
    public function index()
    {
        return view('documentation.layouts.master', [
            'apibUrl' => 'documentation.apib',
        ]);
    }

    public function getApiaryDocumentation()
    {
        $apib = $this->getDocumentationApib(env('API_HOST'));

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
