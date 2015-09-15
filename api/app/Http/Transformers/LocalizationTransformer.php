<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Transformers;

class LocalizationTransformer extends EloquentModelTransformer
{
    /**
     * Transform all translated attributes for model.
     *
     * @param  \Spira\Model\Model\BaseModel $item
     *
     * @return mixed
     */
    public function transformCollection($item)
    {
        $item = $item->getLocalizedAttributes();

        return $this->getService()->item($item, $this);
    }

    /**
     * Transform a translated attributes for model.
     *
     * @param  array $item
     *
     * @return mixed
     */
    public function transformItem($item)
    {
        return $this->getService()->item($item, $this);
    }
}
