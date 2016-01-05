<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Traits;

/**
 * Interface CommentableInterface
 * A model should implement this interface if it uses CommentableTrait.
 */
interface CommentableInterface
{
    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Get the title which will be set as the discussion title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the excerpt which will be set as the discussion excerpt.
     *
     * @return string
     */
    public function getExcerpt();
}
