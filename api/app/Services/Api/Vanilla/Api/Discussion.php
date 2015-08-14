<?php

namespace App\Services\Api\Vanilla\Api;

class Discussion extends ApiAbstract
{
    /**
     * List all discussions.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-all-discussions
     *
     * @param int $page
     *
     * @return array
     */
    public function all($page = 1)
    {
        return $this->get('discussions', ['page' => 'p'.$page]);
    }

    /**
     * Create a new discussion.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#create-a-new-discussion
     *
     * @param string $name
     * @param string $body
     * @param int    $categoryId
     *
     * @return array
     */
    public function create($name, $body, $categoryId)
    {
        $parameters = [
            'Name' => $name,
            'Body' => $body,
            'CategoryID' => $categoryId
        ];

        return $this->post('discussions', $parameters);
    }
}
