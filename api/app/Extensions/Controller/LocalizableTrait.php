<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Controller;

use App\Models\Localization;
use Illuminate\Http\Request;
use Spira\Model\Model\BaseModel;
use Spira\Model\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Spira\Responder\Response\ApiResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Request as RequestFacade;

trait LocalizableTrait
{
    public function getAllLocalizations(Request $request, $id)
    {
        $collection = Localization::where('localizable_id', '=', $id)->get();

        if ($collection->count() < 1) {
            throw new NotFoundHttpException('No localizations found for entity.');
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($collection);
    }

    public function getOneLocalization(Request $request, $id, $region)
    {
        if (! $model = $this
            ->findOrFailEntity($id)
            ->localizations()
            ->where('region_code', $region)->first()) {
            throw new NotFoundHttpException(sprintf('No localizations found for region `%s`.', $region));
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->item($model);
    }

    public function putOneLocalization(Request $request, $id, $region)
    {
        $localizations = $request->json()->all();

        $model = $this->findOrFailEntity($id);

        // Check to see if parameters exist in model
        foreach ($localizations as $parameter => $localization) {
            if (! $model->isFillable($parameter)) {
                throw new ValidationException(
                    new MessageBag([$parameter => 'Localization for this parameter is not allowed.'])
                );
            }
        }

        // Validate the region
        $regionCode = ['region_code' => $region];
        $this->validateRequest($regionCode, Localization::getValidationRules($id));

        // Localizations are partial updates so only validate the fields which were sent with the request
        $this->validateRequest($localizations, $model->getValidationRules($id), null, true);

        $model->localizations()->updateOrCreate($regionCode, array_merge(
            $regionCode, ['localizations' => json_encode($localizations)]
        ))->save();

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdItem($model);
    }

    public function putOneChildLocalization(Request $request, $id, $childId, $region)
    {
        $localizations = $request->json()->all();

        $parent = $this->findParentEntity($id);
        $childModel = $this->findOrFailChildEntity($childId, $parent);

        $createdLocalization = $this->validateAndSaveLocalizations($childModel, $localizations, $region);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdItem($createdLocalization);
    }

    /**
     * Check headers to see if we should localize the content, if so, create a new response and get it to do the
     * localization.
     *
     * @return ApiResponse
     */
    public function getResponse()
    {
        $apiResponse = new ApiResponse();

        if ($region = RequestFacade::header('Accept-Region')) {
            $apiResponse->setLocalizationRegion($region);
        }

        return $apiResponse;
    }

    /**
     * @param BaseModel $model
     * @param $localizations
     * @param $region
     */
    protected function validateAndSaveLocalizations(BaseModel $model, $localizations, $region)
    {
        // Check to see if parameters exist in model
        foreach ($localizations as $parameter => $localization) {
            if (!$model->isFillable($parameter)) {
                throw new ValidationException(
                    new MessageBag([$parameter => 'Localization for this parameter is not allowed.'])
                );
            }
        }

        // Validate the region
        $regionCode = ['region_code' => $region];
        parent::validateRequest($regionCode, Localization::getValidationRules(null));

        // Localizations are partial updates so only validate the fields which were sent with the request
        $this->validateRequest($localizations, $this->getValidationRules($model->getKey()), null, true);

        return $model
            ->localizations()
            ->updateOrCreate($regionCode, array_merge($regionCode, [
                'localizations' => $localizations
            ]))
            ->save();
    }

}