<?php

class ApiDiscussionController extends VanillaController
{
    /**
     * Models to include.
     *
     * @var array
     */
    public $Uses = ['DiscussionModel'];

    /**
     * Discussion object.
     *
     * @var DiscussionModel
     */
    public $DiscussionModel;

    /**
     * Get a single discussion.
     *
     * @param  int $foreignId
     *
     * @return void
     */
    public function getByForeignId($foreignId = '')
    {
        // Load the discussion record
        $foreignId = ($this->isValidUuid($foreignId)) ? $foreignId : 0;
        if (!array_key_exists('Discussion', $this->Data)) {
            $this->setData('Discussion', $this->DiscussionModel->getForeignID($foreignId), true);
        }

        if (!is_object($this->Discussion)) {
            throw notFoundException('Discussion');
        }

        $this->render();
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
