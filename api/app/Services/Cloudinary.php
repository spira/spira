<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services;

use Cloudinary\Api;
use App\Exceptions\NotImplementedException;

class Cloudinary
{
    public $apiKey;
    protected $apiSecret;
    public $cloudName;
    /** @var  \Cloudinary */
    protected $cloudinary;
    /** @var  Api */
    protected $api;

    public function __construct(\Cloudinary $cloudinary, Api $cloudinaryApi)
    {
        $this->apiKey = env('CLOUDINARY_API_KEY');
        $this->apiSecret = env('CLOUDINARY_API_SECRET');
        $this->cloudName = env('CLOUDINARY_CLOUD_NAME');

        \Cloudinary::config(
            [
                'cloud_name' => $this->cloudName,
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
                'private_cdn' => false,
            ]
        );

        $this->cloudinary = $cloudinary;

        $this->api = $cloudinaryApi;

        if (! $this->apiSecret || ! $this->apiKey) {
            throw new NotImplementedException('Cloudinary configuration variables have not been set');
        }
    }

    /**
     * Sign request parameters.
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
        return $this->api->resources();
    }
}
