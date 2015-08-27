<?php

namespace App\Services\Api\Vanilla\Api;

class Discussion extends ApiAbstract
{
    /**
     * List all discussions.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-all-discussions
     *
     * @param  int $page
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
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#create-a-new-discussion
     *
     * @param  string $name
     * @param  string $body
     * @param  int    $categoryId
     * @param  array  $additional
     *
     * @return array
     */
    public function create($name, $body, $categoryId, array $additional = [])
    {
        $parameters = [
            'Name' => $name,
            'Body' => $body,
            'CategoryID' => $categoryId
        ];

        $parameters = array_merge($parameters, $additional);

        return $this->post('discussions', $parameters);
    }

    /**
     * Find a discussion.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#find-a-discussion
     *
     * @param  int $id
     * @param  int $page
     *
     * @return array
     */
    public function find($id, $page = 1)
    {
        return $this->get('discussions/'.$id, ['page' => 'p'.$page]);
    }

    /**
     * Find a discussion by foreign id.
     *
     * @param  string $id
     * @param  int    $page
     * @param  int    $perPage
     *
     * @return array
     */
    public function findByForeignId($id, $page = 1, $perPage = 10)
    {
        return $this->get(
            'discussions/foreign/'.$id,
            ['page' => 'p'.$page, 'perPage' => $perPage]
        );
    }

    /**
     * Update a discussion.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#update-a-discussion
     *
     * @param  int    $id
     * @param  string $name
     * @param  string $body
     *
     * @return array
     */
    public function update($id, $name, $body)
    {
        $parameters = [
            'Name' => $name,
            'Body' => $body
        ];

        return $this->put('discussions/'.$id, $parameters);
    }

    /**
     * Remove a discussion.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#remove-a-discussion
     *
     * @param  int $id
     *
     * @return array
     */
    public function remove($id)
    {
        return $this->delete('discussions/'.$id);
    }

    /**
     * Remove a discussion by foreign id.
     *
     * @param  string $id
     *
     * @return array
     */
    public function removeByForeignId($id)
    {
        return $this->delete('discussions/foreign/'.$id);
    }
}
