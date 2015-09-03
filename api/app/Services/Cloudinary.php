<?php namespace App\Services;

use Cloudinary\Api;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Exceptions\NotImplementedException;

class Cloudinary
{
    public $apiKey;
    protected $apiSecret;
    /** @var  \Cloudinary */
    protected $cloudinary;
    /** @var  Api */
    protected $api;

    public function __construct()
    {
        $this->apiKey = env('CLOUDINARY_API_KEY');
        $this->apiSecret = env('CLOUDINARY_API_SECRET');

        \Cloudinary::config(
            [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
                'private_cdn' => false,
            ]
        );

        $this->cloudinary = new \Cloudinary();

        $this->api = new Api();

        if (!$this->apiSecret || !$this->apiKey) {
            throw new NotImplementedException("Cloudinary configuration variables have not been set");
        }
    }

    /**
     * Sign request parameters
     * @param array $paramsToSign
     * @return string
     */
    public function signRequestParams(array $paramsToSign)
    {

        return $this->cloudinary->api_sign_request($paramsToSign, $this->apiSecret);
    }

    /**
     * @return Api\Response
     */
    public function getRemoteImages()
    {

        $imageResponse = $this->api->resources();


        return $imageResponse;
    }

}
