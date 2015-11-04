<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Model\Model;

/**
 * A model needs to implement these methods to work in a relationship without
 * Eloquent trying to hit the database. To be used for models that doesn't
 * actually exist in the database but still needs to have a defined
 * relationship with a model that does exist in the database.
 */
interface LocalizableModelInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function localizations();
}
