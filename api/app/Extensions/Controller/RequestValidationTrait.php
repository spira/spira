<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 04.08.15
 * Time: 21:09
 */

namespace App\Extensions\Controller;

use App\Exceptions\ValidationException;
use App\Exceptions\ValidationExceptionCollection;
use Laravel\Lumen\Routing\ValidatesRequests;
use Spira\Repository\Collection\Collection;

trait RequestValidationTrait
{
    use ValidatesRequests;

    /**
     * @param $entityCollection
     * @param string $keyName
     * @param bool $validate
     * @param string $rule
     * @return array
     * @throws ValidationExceptionCollection
     */
    protected function getIds($entityCollection, $keyName, $validate, $rule)
    {
        $ids = [];
        $errors = [];
        $error = false;
        foreach ($entityCollection as $requestEntity) {
            if (isset($requestEntity[$keyName]) && $requestEntity[$keyName]) {
                try {
                    $id = $requestEntity[$keyName];
                    if ($validate) {
                        $this->validateId($id, $keyName, $rule);
                    }
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
     * @param string $rule
     * @throw ValidationException
     */
    protected function validateId($id, $keyName, $rule)
    {
        $validation = $this->getValidationFactory()->make([$keyName=>$id], [$keyName=>$rule]);
        if ($validation->fails()) {
            throw new ValidationException($validation->getMessageBag());
        }
    }
}