<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers\Traits;

use App\Models\Tag;
use Illuminate\Http\Request;
use Spira\Core\Contract\Exception\NotImplementedException;
use Spira\Core\Model\Collection\Collection;

trait TagCategoryTrait
{
    /**
     * Get the root tag name; it must be set in the implementing controller.
     * @return mixed
     */
    public function getRootTagName()
    {
        if (! isset($this->rootCategoryTagName)) {
            throw new NotImplementedException('Controller using '.self::class.' must have property `rootCategoryTagName` defined');
        }

        return $this->rootCategoryTagName;
    }

    /**
     * Get all tags for the category.
     * @param Request $request
     * @return mixed
     */
    public function getAllTagCategories(Request $request)
    {
        $collection = $this->getTagsFromRoot($this->getRootTagName());
        $collection = $this->getWithNested($collection, $request);
        $this->checkPermission(static::class.'@getAllTags', ['model' => $collection]);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($collection);
    }

    /**
     * Get the child tags from the root tag name.
     * @param $rootTagName
     * @return Collection
     */
    public function getTagsFromRoot($rootTagName)
    {
        /** @var Tag $rootTag */
        $rootTag = Tag::where('tag', '=', $rootTagName)->firstOrFail();

        return $rootTag->childTags()->get();
    }
}
