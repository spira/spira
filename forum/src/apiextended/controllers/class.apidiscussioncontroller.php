<?php

class ApiDiscussionController extends DiscussionController
{
    /**
     * Get a single discussion.
     *
     * @param  string $foreignId
     * @param  string $page
     *
     * @throws Gdn_UserException
     *
     * @return void
     */
    public function getByForeignId($foreignId = '', $page = '')
    {
        if (!$this->isValidUuid($foreignId)) {
            throw new Gdn_UserException('Bad Request', 400);
        }

        if (!array_key_exists('Discussion', $this->Data)) {
            $this->setData('Discussion', $this->DiscussionModel->getForeignID($foreignId), true);
        }

        if (!is_object($this->Discussion)) {
            throw notFoundException('Discussion');
        }

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
        if (!$this->isValidUuid($foreignId)) {
            throw new Gdn_UserException('Bad Request', 400);
        }

        $discussion = $this->DiscussionModel->getForeignID($foreignId);

        if (!is_object($discussion)) {
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
        $pattern = '[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}';

        return (bool) preg_match('/^'.$pattern.'$/', $uuid);
    }
}
