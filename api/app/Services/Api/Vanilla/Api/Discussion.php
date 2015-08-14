<?php

namespace App\Services\Api\Vanilla\Api;

class Discussion extends ApiAbstract
{
    /**
     * List all discussions.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-all-discussions
     *
     * @return array
     */
    public function all()
    {
        return $this->get('discussions');
    }
}
