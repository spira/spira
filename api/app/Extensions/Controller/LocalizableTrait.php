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
use Spira\Model\Validation\ValidationException;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait LocalizableTrait
{

    public function getAllLocalizations(Request $request, $id)
    {
        if(!$collection = Localization::where('localizable_id', '=', $id)->get()) {
            throw new NotFoundHttpException('No localizations found for entity.');
        }

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($collection);
    }

    public function getOneLocalization(Request $request, $id, $region)
    {
        if(!$model = $this
            ->findOrFailEntity($id)
            ->localizations()
            ->where('region_code', $region)->first()){
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
        foreach($localizations as $parameter => $localization) {
            if(!$model->isFillable($parameter)) {
                throw new ValidationException(
                    new MessageBag([$parameter => 'Localization for this parameter is not allowed.'])
                );
            }
        }

        // Localizations are partial updates so only validate the fields which were sent with the request
        $this->validateRequest($request->json()->all(), $model->getValidationRules(), true);

        $regionCode = ['region_code' => $region];

        $model->localizations()->updateOrCreate($regionCode, array_merge(
            $regionCode, ['localizations' => json_encode($localizations)]
        ))->save();

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->createdItem($model);
    }

}
