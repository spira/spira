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

    /**
     * Create a discussion.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-a-discussion
     *
     * @param int $id
     *
     * @return array
     */
    public function find($id)
    {
        return $this->get('discussions/'.rawurlencode($id));
    }

    /**
     * Update a discussion.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#update-a-discussion
     *
     * @param int    $id
     * @param string $name
     * @param string $body
     *
     * @return array
     */
    public function update($id, $name, $body)
    {
        $parameters = [
            'Name' => $name,
            'Body' => $body
        ];

        return $this->put('discussions/'.rawurlencode($id), $parameters);
    }

    /**
     * Remove a discussion.
     *
     * @link https://github.com/kasperisager/vanilla-api/wiki/Endpoints#remove-a-discussion
     *
     * @param int $id
     *
     * @return array
     */
    public function remove($id)
    {
        return $this->delete('discussions/'.rawurlencode($id));
    }
}
