<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Spira\Core\Controllers\ApiController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UtilityController extends ApiController
{
    /**
     * Enable permissions checks.
     */
    protected $permissionsEnabled = true;

    public function getSystemInformation()
    {
        $file = 'system-information.json';

        if (!Storage::disk('local')->has($file)) {
            throw new NotFoundHttpException("file $file not found");
        }

        $file = Storage::disk('local')->get($file);
        $data = json_decode($file, true);

        return $this->getResponse()
            ->item($data);
    }

}
