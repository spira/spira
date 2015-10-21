<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Controller;

use Illuminate\Http\Request;
use Spira\Model\Validation\ValidationException;
use Illuminate\Support\MessageBag;

trait LocalizableTrait
{

    public function getAllLocalizations(Request $request, $id)
    {

    }

    public function getOneLocalization(Request $request, $id, $region)
    {

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
