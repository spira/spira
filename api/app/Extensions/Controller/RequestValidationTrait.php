<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 04.08.15
 * Time: 21:09
 */

namespace App\Extensions\Controller;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Lumen\Routing\ValidatesRequests;
use Spira\Model\Validation\ValidationException;
use Spira\Model\Validation\ValidationExceptionCollection;
use Spira\Model\Validation\Validator;

trait RequestValidationTrait
{
    use ValidatesRequests;

    /**
     * @param $entityCollection
     * @param string $keyName
     * @param string|null $rule
     * @return array
     * @throws ValidationExceptionCollection
     */
    protected function getIds($entityCollection, $keyName, $rule = null)
    {
        $ids = [];
        $errors = [];
        $error = false;
        foreach ($entityCollection as $requestEntity) {
            if (isset($requestEntity[$keyName]) && $requestEntity[$keyName]) {
                try {
                    $id = $requestEntity[$keyName];
                    $this->validateId($id, $keyName, $rule);
                    $ids[] = $id;
                    $errors[] = null;
                } catch (ValidationException $e) {
                    $error = true;
                    $errors[] = $e;
                }
            } else {
                $errors[] = null;
            }
        }
        if ($error) {
            throw new ValidationExceptionCollection($errors);
        }

        return $ids;
    }

    /**
     * @param $requestEntity
     * @param $keyName
     * @return null
     */
    protected function getIdOrNull($requestEntity, $keyName)
    {
        return isset($requestEntity[$keyName])?$requestEntity[$keyName]:null;
    }


    /**
     * Build notFoundException
     * @param string $keyName
     * @return ValidationException
     */
    protected function notFoundException($keyName = '')
    {
        $validation = $this->getValidationFactory()->make([$keyName=>$keyName], [$keyName=>'notFound']);
        if (!$validation->fails()) {
            // @codeCoverageIgnoreStart
            throw new \LogicException("Validator should have failed");
            // @codeCoverageIgnoreEnd
        }

        throw new ValidationException($validation->getMessageBag());
    }

    /**
     * Get notFoundManyException
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
     * @param $id
     * @param string $keyName
     * @param string|null $rule
     * @throw ValidationException
     */
    protected function validateId($id, $keyName, $rule = null)
    {
        if (!is_null($rule)) {
            $validation = $this->getValidationFactory()->make([$keyName=>$id], [$keyName=>$rule]);
            if ($validation->fails()) {
                throw new ValidationException($validation->getMessageBag());
            }
        }
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
     * Validate a request collection
     * @param $requestCollection
     * @param $validationRules
     * @param bool|false $limitToKeysPresent
     * @return bool
     */
    public function validateRequestCollection($requestCollection, $validationRules, $limitToKeysPresent = false){

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

}
