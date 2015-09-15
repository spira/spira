<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\LocalizationTransformer;
use App\Models\Localization;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocalizationsController extends EntityController
{
    /**
     * Set dependencies.
     *
     * @param Localization            $model
     * @param LocalizationTransformer $transformer
     */
    public function __construct(Localization $model, LocalizationTransformer $transformer)
    {
        parent::__construct($model, $transformer);
    }

    /**
     * Get a entity's localized attribute for a region.
     *
     * @param  Request $request
     * @param  string  $region
     * @param  string  $id
     * @param  string  $attribute
     *
     * @return ApiResponse
     */
    public function getOne(Request $request, $region, $id, $attribute)
    {
        $model = $this->findOrFailEntity($region, $id);

        if (! $model->hasLocalizedAttribute($attribute)) {
            throw new NotFoundHttpException('Entity attribute does not have localized content.');
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item(array_only($model->getLocalizedAttributes(), [$attribute]));
    }

    /**
     * Get all entity's localized attributes for a region.
     *
     * @param  Request $request
     * @param  string  $region
     * @param  string  $id
     *
     * @return ApiResponse
     */
    public function getAll(Request $request, $region, $id)
    {
        $model = $this->findOrFailEntity($region, $id);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($model);
    }

    /**
     * Find entity by compound key.
     *
     * @param string $region
     * @param string $id
     *
     * @return BaseModel
     */
    protected function findOrFailEntity($region, $id)
    {
        try {
            return $this->getModel()->findByCompoundKey($region, $id);
        } catch (ModelNotFoundException $e) {
            throw $this->notFoundException($this->getModel()->getKeyName());
        }
    }
}
