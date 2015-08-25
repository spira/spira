<?php

namespace App\Services\Api\Vanilla\Api;

class Comment extends ApiAbstract
{
    /**
     * Create a new comment.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#create-a-new-comment
     *
     * @param  int    $discussionId
     * @param  string $body
     * @param  string $format
     *
     * @return array
     */
    public function create($discussionId, $body, $format = 'Html')
    {
        $parameters = [
            'Body' => $body,
            'Format' => $format
        ];

        return $this->post('discussions/'.$discussionId.'/comments', $parameters);
    }

    /**
     * Update a comment.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#update-a-comment
     *
     * @param  int    $id
     * @param  string $body
     *
     * @return array
     */
    public function update($id, $body)
    {
        $parameters = [
            'Body' => $body,
        ];

        return $this->put('discussions/comments/'.$id, $parameters);
    }

    /**
     * Remove a comment.
     *
     * @link   https://github.com/kasperisager/vanilla-api/wiki/Endpoints#remove-a-comment
     *
     * @param  int $id
     *
     * @return array
     */
    public function remove($id)
    {
        return $this->delete('discussions/comments/'.$id);
    }
}
