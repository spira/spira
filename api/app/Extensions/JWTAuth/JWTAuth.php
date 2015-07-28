<?php namespace App\Extensions\JWTAuth;

use App;
use Exception;
use RuntimeException;
use Tymon\JWTAuth\Token;
use App\Exceptions\BadRequestException;
use Tymon\JWTAuth\JWTAuth as JWTAuthBase;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\TokenInvalidException;
use App\Exceptions\UnprocessableEntityException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTAuth extends JWTAuthBase
{
    /**
     * Get the token contained within the current request.
     *
     * @throws BadRequestException
     * @return Token
     */
    public function getTokenFromRequest()
    {
        if (!$token = $this->setRequest($this->request)->getToken()) {
            throw new BadRequestException('Token not provided.');
        }

        return $token;
    }

    /**
     * Get the user from the request token.
     *
     * @throws UnauthorizedException
     * @throws UnprocessableEntityException
     * @throws RuntimeException
     * @return \App\Model\User
     */
    public function getUser()
    {
        $token = $this->getTokenFromRequest();

        try {
            $user = $this->authenticate((string) $token);
        } catch (TokenExpiredException $e) {
            throw new UnauthorizedException('Token expired.', 401, $e);
        } catch (TokenInvalidException $e) {
            throw new UnprocessableEntityException($e->getMessage(), 422, $e);
        }

        if (!$user) {
            throw new RuntimeException('The user does not exist.');
        }

        return $user;
    }

    /**
     * Helper to get current user with null if none instead of exceptions.
     *
     * @return \App\Models\User|null
     */
    public function user()
    {
        try {
            $user = $this->getUser();
        } catch (Exception $e) {
            $user = null;
        }

        return $user;
    }
}
