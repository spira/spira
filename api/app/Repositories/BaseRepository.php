<?php namespace App\Repositories;

use App\Exceptions\ValidationException;
use App\Exceptions\ValidationExceptionCollection;
use Spira\Repository\Collection\Collection;
use Spira\Repository\Model\BaseModel;
use Spira\Repository\Repository\RepositoryException;
use Traversable;

abstract class BaseRepository extends \Spira\Repository\Repository\BaseRepository
{
    /**
     * @param Collection|BaseModel[] $models
     * @return BaseModel[]
     * @throws \Exception some general exception
     * @throws RepositoryException
     */
    public function saveMany($models)
    {
        if (!is_array($models) && !($models instanceof Traversable)) {
            throw new RepositoryException('Models must be either an array or Collection with Traversable');
        }

        $this->getConnection()->beginTransaction();

        try {
            $error = false;
            $errors = [];
            /** @var BaseModel $models */
            foreach ($models as $model) {
                try {
                    if (!$this->save($model)) {
                        throw new RepositoryException('Massive assignment failed as model with id '.$model->getQueueableId().' couldn\'t be saved');
                    }
                    $errors[] = null;
                } catch (ValidationException $e) {
                    $error = true;
                    $errors[] = $e->getErrors();
                }
            }
            if ($error) {
                throw new ValidationExceptionCollection($errors);
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return $models;
    }

    /**
     * Delete a collection of entities.
     *
     * @param  Collection|BaseModel[] $models
     * @throws \Exception
     * @return bool
     */
    public function deleteMany($models)
    {
        if (!is_array($models) && !($models instanceof Traversable)) {
            throw new RepositoryException('Models must be either an array or Collection with Traversable');
        }

        $this->getConnection()->beginTransaction();

        try {
            /** @var BaseModel $models */
            foreach ($models as $model) {
                if (!$this->delete($model)) {
                    throw new RepositoryException('Massive deletion failed as model with id '.$model->getQueueableId().' couldn\'t be deleted');
                }
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return true;
    }
}
