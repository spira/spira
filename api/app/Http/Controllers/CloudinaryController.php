<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Services\Cloudinary;
use Illuminate\Http\Request;
use Spira\Core\Controllers\ApiController;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class CloudinaryController extends ApiController
{
    /** @var Cloudinary */
    protected $cloudinary;

    /**
     * Assign dependencies.
     *
     * @param Cloudinary $cloudinary
     * @param EloquentModelTransformer $transformer
     */
    public function __construct(Cloudinary $cloudinary, EloquentModelTransformer $transformer)
    {
        $this->cloudinary = $cloudinary;
        parent::__construct($transformer);
    }

    public function getSignature(Request $request)
    {
        $signatureParams = $request->query();
        $signedString = $this->cloudinary->signRequestParams($signatureParams);

        $responseObject = [
            'signature' => $signedString,
            'api_key' => $this->cloudinary->apiKey,
        ];

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($responseObject);
    }
}
