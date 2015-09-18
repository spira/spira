<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Auth\Token;

use App\Exceptions\TokenInvalidException;
use Exception;
use Namshi\JOSE\JWS;

/**
 * The provided NamshiAdapter in the package can not handle RSA keys, which
 * this adapter enables.
 */
class NamshiAdapter  implements JWTInterface
{
    /**
     * @var \Namshi\JOSE\JWS
     */
    protected $jws;

    /**
     * @var string
     */
    protected $algo;
    /**
     * @var mixed
     */
    private $secretPrivate;
    /**
     * @var mixed
     */
    private $secretPublic;

    /**
     * @param mixed $secretPublic
     * @param mixed $secretPrivate
     * @param string $algo
     * @param null $driver
     */
    public function __construct($secretPublic, $secretPrivate, $algo, $driver = null)
    {
        $this->algo = $algo;

        $this->jws = $driver ?: new JWS(['alg' => $algo]);
        $this->secretPrivate = $secretPrivate;
        $this->secretPublic = $secretPublic;
    }

    /**
     * Create a JSON Web Token.
     *
     * @throws TokenInvalidException
     *
     * @return string
     */
    public function encode(array $payload)
    {
        try {
            $this->jws->setPayload($payload)->sign($this->secretPrivate);

            return $this->jws->getTokenString();
        } catch (Exception $e) {
            throw new TokenInvalidException('Could not create token: '.$e->getMessage());
        }
    }

    /**
     * Decode a JSON Web Token.
     *
     * @param string $token
     *
     * @throws TokenInvalidException
     *
     * @return array
     */
    public function decode($token)
    {
        try {
            $jws = JWS::load($token);
        } catch (Exception $e) {
            throw new TokenInvalidException('Could not decode token: '.$e->getMessage(), null, $e);
        }

        if (! $jws->verify($this->secretPublic, $this->algo)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        return $jws->getPayload();
    }
}
