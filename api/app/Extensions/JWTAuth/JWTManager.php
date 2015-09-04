<?php

namespace App\Extensions\JWTAuth;

use Tymon\JWTAuth\Token;
use Tymon\JWTAuth\JWTManager as JWTManagerBase;

class JWTManager extends JWTManagerBase
{
    /**
     * Refresh a Token and return a new Token.
     *
     * @param  \Tymon\JWTAuth\Token  $token
     * @return \Tymon\JWTAuth\Token
     */
    public function refresh(Token $token)
    {
        $payload = $this->setRefreshFlow()->decode($token);

        if ($this->blacklistEnabled) {
            // invalidate old token
            $this->blacklist->add($payload);
        }

        // return the new token
        return $this->encode(
            $this->payloadFactory->make([
                'sub' => $payload['sub'],
                'iat' => $payload['iat'],
                '_user' => $payload['_user'],
            ])
        );
    }
}
