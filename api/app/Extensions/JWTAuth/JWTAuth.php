<?php namespace App\Extensions\JWTAuth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\JWTAuth as JWTAuthBase;

use RuntimeException;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\TokenInvalidException;
use App\Exceptions\UnprocessableEntityException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class JWTAuth extends JWTAuthBase
{
    /**
     * Get the token contained within the current request.
     *
     * @param  Request  $request
     * @throws BadRequestException
     * @return Token
     */
    public function getTokenFromRequest(Request $request)
    {
        if (!$token = $this->setRequest($request)->getToken()) {
            throw new BadRequestException('Token not provided.');
        }

        return $token;
    }

    /**
     * Get the user from the token, with validation checking.
     *
     * @param  Token  $token
     * @throws UnauthorizedException
     * @throws UnprocessableEntityException
     * @throws RuntimeException
     * @return \App\Model\User
     */
    public function getUser(Token $token)
    {
        try {
            $user = $this->authenticate((string) $token);
        }
        catch (TokenExpiredException $e) {
            throw new UnauthorizedException('Token expired.', 401, $e);
        }
        catch (TokenInvalidException $e) {
            throw new UnprocessableEntityException($e->getMessage(), 422, $e);
        }

        if (!$user) {
            throw new RuntimeException('The user does not exist.');
        }

        return $user;
    }
}
