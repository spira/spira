<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

class ApiDiscussionController extends DiscussionController
{
    /**
     * Get a single discussion.
     *
     * @param  string $foreignId
     * @param  string $page
     * @param  int    $perPage
     *
     * @throws Gdn_UserException
     *
     * @return void
     */
    public function getByForeignId($foreignId = '', $page = '', $perPage = 10)
    {
        if (! $this->isValidUuid($foreignId)) {
            throw new Gdn_UserException('Bad Request', 400);
        }

        if (! array_key_exists('Discussion', $this->Data)) {
            $this->setData('Discussion', $this->DiscussionModel->getForeignID($foreignId), true);
        }

        if (! is_object($this->Discussion)) {
            throw notFoundException('Discussion');
        }

        // Override the per page value with a custom setting
        saveToConfig('Vanilla.Comments.PerPage', (int) $perPage, false);
        $commentsPerPage = c('Vanilla.Comments.PerPage');

        // Add comments per page to the response
        $this->setData('CommentsPerPage', $commentsPerPage);

        $this->index($this->Discussion->DiscussionID, '', $page);
    }

    /**
     * Delete a single discussion.
     *
     * @param  string $foreignId
     *
     * @throws Gdn_UserException
     *
     * @return void
     */
    public function deleteByForeignId($foreignId = '')
    {
        if (! $this->isValidUuid($foreignId)) {
            throw new Gdn_UserException('Bad Request', 400);
        }

        $discussion = $this->DiscussionModel->getForeignID($foreignId);

        if (! is_object($discussion)) {
            throw notFoundException('Discussion');
        }

        $this->delete($discussion->DiscussionID);
    }

    /**
     * Checks that a string appears to be in the format of a UUID.
     *
     * @param  string $uuid
     *
     * @return bool
     */
    protected function isValidUuid($uuid)
    {
        $pattern = '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$';

        return (bool) preg_match('/^'.$pattern.'$/', $uuid);
    }
}
