<?php

namespace App\Extensions\JWTAuth;

use Exception;
use App\Models\User;
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
        if (! $token = $this->setRequest($this->request)->getToken()) {
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
     * @return User
     */
    public function getUser()
    {
        $token = $this->getTokenFromRequest();

        try {
            $user = $this->authenticate((string) $token);
        } catch (TokenExpiredException $e) {
            throw new UnauthorizedException('Token expired.', null, $e);
        } catch (TokenInvalidException $e) {
            throw new UnprocessableEntityException($e->getMessage(), null, $e);
        }

        if (! $user) {
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

    /**
     * Generate a token using the user identifier as the subject claim.
     *
     * @param mixed $user
     * @param array $customClaims
     *
     * @return string
     */
    public function fromUser($user, array $customClaims = [])
    {
        $payload = $this->makePayload($user, $customClaims);

        return $this->manager->encode($payload)->get();
    }

    /**
     * Create a Payload instance.
     *
     * @param mixed $subject
     * @param array $customClaims
     *
     * @return \Tymon\JWTAuth\Payload
     */
    protected function makePayload($user, array $customClaims = [])
    {
        return $this->manager->getPayloadFactory()->make(
            array_merge($customClaims, [
                'sub' => $user->user_id,
                '_user' => $user,
            ])
        );
    }
}
