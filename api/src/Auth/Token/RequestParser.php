<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 15.09.15
 * Time: 14:55
 */

namespace Spira\Auth\Token;


use Illuminate\Http\Request;

class RequestParser
{

    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $header;
    /**
     * @var string
     */
    private $query;

    /**
     * @param string $method
     * @param string $header
     * @param string $query
     */
    public function __construct($method = 'bearer', $header = 'authorization', $query = 'token')
    {
        $this->method = $method;
        $this->header = $header;
        $this->query = $query;
    }

    /**
     * @param Request $request
     * @return array|string
     * @throws TokenIsMissingException
     */
    public function getToken(Request $request)
    {
        $header = $request->header($this->header);


        if (starts_with(strtolower($header), $this->method)) {
            return trim(str_ireplace($this->method, '', $header));
        }

        if ($token = $token = $request->query($this->query, false)){
            return $token;
        }

        throw new TokenIsMissingException();

    }



}