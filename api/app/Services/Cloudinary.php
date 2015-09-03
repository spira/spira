<?php namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Exceptions\NotImplementedException;

class Cloudinary
{

    public $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->apiKey = env('CLOUDINARY_API_KEY');
        $this->apiSecret = env('CLOUDINARY_API_SECRET');

        if (!$this->apiSecret || !$this->apiKey){
            throw new NotImplementedException("Cloudinary configuration variables have not been set");
        }

    }

    /**
     * Sign a request string
     * @param $uploadString string
     * @return string
     */
    public function signUploadString($uploadString)
    {

        $signaturePlain = sprintf('%s%s', $uploadString, $this->apiSecret);

        return sha1($signaturePlain);
    }

}
