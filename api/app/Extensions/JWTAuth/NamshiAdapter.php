<?php

namespace app\Extensions\JWTAuth;

use App\Exceptions\TokenInvalidException;
use Exception;
use Namshi\JOSE\JWS;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Providers\JWT\JWTInterface;
use Tymon\JWTAuth\Providers\JWT\JWTProvider;

/**
 * The provided NamshiAdapter in the package can not handle RSA keys, which
 * this adapter enables.
 */
class NamshiAdapter extends JWTProvider implements JWTInterface
{
    /**
     * @var \Namshi\JOSE\JWS
     */
    protected $jws;

    /**
     * @param string $secret
     * @param string $algo
     * @param null   $driver
     */
    public function __construct($secret, $algo, $driver = null)
    {
        array_walk($secret, function (&$path) {
            $path = 'file://'.$path;
        });

        parent::__construct($secret, $algo);

        $this->jws = $driver ?: new JWS(['alg' => $algo]);
    }

    /**
     * Create a JSON Web Token.
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     *
     * @return string
     */
    public function encode(array $payload)
    {
        try {
            $this->jws->setPayload($payload)->sign($this->secret['private']);

            return $this->jws->getTokenString();
        } catch (Exception $e) {
            throw new JWTException('Could not create token: '.$e->getMessage());
        }
    }

    /**
     * Decode a JSON Web Token.
     *
     * @param string $token
     *
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     *
     * @return array
     */
    public function decode($token)
    {
        try {
            $jws = JWS::load($token);
        } catch (Exception $e) {
            throw new TokenInvalidException('Could not decode token: '.$e->getMessage(), 500, $e);
        }

        if (!$jws->verify($this->secret['public'], $this->algo)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        return $jws->getPayload();
    }
}
