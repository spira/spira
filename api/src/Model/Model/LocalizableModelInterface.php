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
 * Interface LocalizableModelInterface
 * A model should implement this interface if it uses LocalizableModelTrait to get correct typehinting
 * @package Spira\Model\Model
 */
interface LocalizableModelInterface
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function localizations();
}
