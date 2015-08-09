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
     * Save helper method
     * Some additional savings can happen inside model class
     * So we need to wrap it into transaction
     *
     * @param BaseModel $model
     * @return BaseModel|false
     * @throws \Exception
     */
    public static function save(BaseModel $model)
    {
        /** @var BaseModel $model */
        $model->getConnection()->beginTransaction();

        try {
            $model->push();
        } catch (\Exception $e) {
            $model->getConnection()->rollBack();
            throw $e;
        }

        $model->getConnection()->commit();
        return $model;
    }

    /**
     * @param BaseModel[] $models
     * @return BaseModel[]
     * @throws \Exception some general exception
     */
    public static function saveMany($models)
    {
        $connection = null;
        /** @var BaseModel $model */
        if (count($models) && $model = current($models)){
            $connection = $model->getConnection();
        }

        if (!$connection){
            throw new \InvalidArgumentException('No connection provided');
        }


        $connection->beginTransaction();

        try {
            $error = false;
            $errors = [];
            /** @var BaseModel $models */
            foreach ($models as $model) {
                try {
                    static::save($model);
                    $errors[] = null;
                } catch (ValidationException $e) {
                    $error = true;
                    $errors[] = $e;
                }
            }
            if ($error) {
                throw new ValidationExceptionCollection($errors);
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();
        return $models;
    }


    /**
     * Delete an entity by id.
     *
     * @param BaseModel $model
     * @return bool
     * @throws \Exception
     */
    public static function delete(BaseModel $model)
    {
        /** @var BaseModel $model */
        $model->getConnection()->beginTransaction();

        try {
            $result = $model->delete();
        } catch (\Exception $e) {
            $model->getConnection()->rollBack();
            throw $e;
        }

        $model->getConnection()->commit();
        return $result;
    }

    /**
     * Delete a collection of entities.
     *
     * @param  BaseModel[] $models
     * @throws \Exception
     * @return bool
     */
    public static function deleteMany($models)
    {
        $connection = null;
        /** @var BaseModel $model */
        if (count($models) && $model = current($models)){
            $connection = $model->getConnection();
        }

        if (!$connection){
            throw new \InvalidArgumentException('No connection provided');
        }

        $connection->beginTransaction();

        try {
            /** @var BaseModel $models */
            foreach ($models as $model) {
                static::delete($model);
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $connection->commit();
        return true;
    }
}