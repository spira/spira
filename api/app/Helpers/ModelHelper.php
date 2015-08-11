<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 10.08.15
 * Time: 0:18
 */

namespace App\Helpers;


use Spira\Repository\Model\BaseModel;
use Spira\Repository\Validation\ValidationException;
use Spira\Repository\Validation\ValidationExceptionCollection;

class ModelHelper
{
    /**
     * @param BaseModel[] $models
     * @return BaseModel[]
     * @throws \Exception some general exception
     */
    public static function saveMany($models)
    {
        $error = false;
        $errors = [];
        /** @var BaseModel $models */
        foreach ($models as $model) {
            try {
                $model->push();
                $errors[] = null;
            } catch (ValidationException $e) {
                $error = true;
                $errors[] = $e;
            }
        }
        if ($error) {
            throw new ValidationExceptionCollection($errors);
        }

        return $models;
    }
}