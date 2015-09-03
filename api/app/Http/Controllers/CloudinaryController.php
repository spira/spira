<?php namespace App\Http\Controllers;

use App\Http\Transformers\EloquentModelTransformer;
use App\Services\Cloudinary;
use App\Services\Datasets\Countries;
use Illuminate\Http\Request;

class CloudinaryController extends ApiController
{
    /** @var Cloudinary */
    protected $cloudinary;

    /**
     * Assign dependencies
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



    public function getAll()
    {
        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($this->countries->all());
    }
}
