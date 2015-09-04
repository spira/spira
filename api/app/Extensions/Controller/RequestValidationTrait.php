<?php

/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 04.08.15
 * Time: 21:09.
 */

namespace App\Extensions\Controller;

use App\Exceptions\BadRequestException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ValidatesRequests;
use Spira\Model\Model\BaseModel;
use Spira\Model\Validation\ValidationException;
use Spira\Model\Validation\ValidationExceptionCollection;
use Spira\Model\Validation\Validator;

trait RequestValidationTrait
{
    use ValidatesRequests;

    /**
     * @param $entityCollection
     * @param string $keyName
     * @return array
     */
    public function getIds($entityCollection, $keyName)
    {
        $ids = [];
        foreach ($entityCollection as $requestEntity) {
            if (isset($requestEntity[$keyName]) && $requestEntity[$keyName]) {
                $ids[] = $requestEntity[$keyName];
            }
        }

        return $ids;
    }

    /**
     * Build notFoundException.
     * @param string $keyName
     * @return ValidationException
     */
    protected function notFoundException($keyName = '')
    {
        $validation = $this->getValidationFactory()->make([$keyName => $keyName], [$keyName => 'notFound']);
        if (! $validation->fails()) {
            // @codeCoverageIgnoreStart
            throw new \LogicException('Validator should have failed');
            // @codeCoverageIgnoreEnd
        }

        throw new ValidationException($validation->getMessageBag());
    }

    /**
     * Get notFoundManyException.
     * @param $ids
     * @param Collection $models
     * @param string $keyName
     * @return ValidationExceptionCollection
     */
    protected function notFoundManyException($ids, $models, $keyName = '')
    {
        $errors = [];
        foreach ($ids as $id) {
            if ($models->get($id)) {
                $errors[] = null;
            } else {
                try {
                    throw $this->notFoundException($keyName);
                } catch (ValidationException $e) {
                    $errors[] = $e;
                }
            }
        }

        throw new ValidationExceptionCollection($errors);
    }

    /**
     * @param $requestEntity
     * @param array $validationRules
     * @param bool $limitToKeysPresent
     * @return bool
     */
    public function validateRequest($requestEntity, $validationRules, $limitToKeysPresent = false)
    {
        if ($limitToKeysPresent) {
            $validationRules = array_intersect_key($validationRules, $requestEntity);
        }

        /** @var Validator $validation */
        $validation = $this->getValidationFactory()->make($requestEntity, $validationRules);

        if ($validation->fails()) {
            throw new ValidationException($validation->messages());
        }

        return true;
    }

    /**
     * Validate a request collection.
     * @param $requestCollection
     * @param $validationRules
     * @param bool|false $limitToKeysPresent
     * @return bool
     */
    public function validateRequestCollection($requestCollection, $validationRules, $limitToKeysPresent = false)
    {
        $errorCaught = false;
        $errors = [];

        foreach ($requestCollection as $requestEntity) {
            try {
                $this->validateRequest($requestEntity, $validationRules, $limitToKeysPresent);
                $errors[] = null;
            } catch (ValidationException $e) {
                $errors[] = $e;
                $errorCaught = true;
            }
        }

        if ($errorCaught) {
            throw new ValidationExceptionCollection($errors);
        }

        return true;
    }

    /**
     * @param Request $request
     * @param $id
     * @param BaseModel $model
     * @param bool|true $requireEntityKey
     * @return bool
     */
    protected function checkEntityIdMatchesRoute(Request $request, $id, BaseModel $model, $requireEntityKey = true)
    {
        $keyName = $model->getKeyName();
        if (! $request->has($keyName)) {
            if (! $requireEntityKey) {
                return true; //it is ok if the key is not set (for patch requests etc)
            } else {
                throw new BadRequestException("Request entity must include entity id ($keyName) for ".get_class($model));
            }
        }

        if ((string) $request->input($keyName) !== (string) $id) {
            throw new BadRequestException('Provided entity body does not match route parameter. The entity key cannot be updated');
        }

        return true;
    }
}
