<?php namespace Spira\Repository\Repository;

use App\Models\BaseModel;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Repository\Collection\Collection;
use Spira\Repository\Validation\ValidationException;
use Spira\Repository\Validation\ValidationExceptionCollection;
use Traversable;

abstract class BaseRepository
{
    /**
     * Eloquent Model
     *
     * @var BaseModel
     */
    protected $model;
    /**
     * @var ConnectionResolverInterface
     */
    protected $connectionResolver;

    /**
     * Name of the connection for the repo
     * @var string
     */
    protected $connectionName;

    /**
     * @var string
     */
    private $modelClassName;


    /**
     * Assign dependencies.
     *
     * @param  ConnectionResolverInterface $connectionResolver
     * @throws RepositoryException
     */
    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        $this->model = $this->model();
        if (!$this->model instanceof BaseModel) {
            throw new RepositoryException("Class {$this->getModelClassName()} must be an instance of ".BaseModel::class);
        }
        $this->connectionResolver = $connectionResolver;
    }


    /**
     * Get an entity by id.
     *
     * @param  string  $id
     * @param  array   $columns
     * @return BaseModel|null
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }


    /**
     * Find a model by its primary key.
     *
     * @param  array $ids
     * @param  array $columns
     * @param null $skip
     * @param null $take
     * @return Collection
     */
    public function findMany($ids, $columns = ['*'], $skip = null, $take = null)
    {
        return $this->model->skip($skip)->take($take)->findMany($ids, $columns);
    }


    /**
     * Get all entities.
     *
     * @param  array $columns
     * @param null $skip
     * @param null $take
     * @return Collection
     */
    public function all($columns = ['*'], $skip = null, $take = null)
    {
        return $this->model->skip($skip)->take($take)->get($columns);
    }

    /**
     * @param BaseModel $model
     * @return BaseModel|false
     * @throws RepositoryException
     * @throws \Exception
     */
    public function save(BaseModel $model)
    {
        $modelClassName = $this->getModelClassName();
        if (!($model instanceof $modelClassName)) {
            throw new RepositoryException('provided model is not instance of '.$modelClassName);
        }
        /** @var BaseModel $model */
        $this->getConnection()->beginTransaction();

        try {
            if (!$model->push()) {
                throw new RepositoryException('couldn\'t save model');
            }
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return $model;
    }

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
                    $errors[] = $e;
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
     * Delete an entity by id.
     *
     * @param BaseModel $model
     * @return bool
     * @throws RepositoryException
     */
    public function delete(BaseModel $model)
    {
        $modelClassName = $this->getModelClassName();
        if (!($model instanceof $modelClassName)) {
            throw new RepositoryException('provided model is not instance of '.$modelClassName);
        }

        /** @var BaseModel $model */

        return $model->delete();
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

    /**
     * Check if an entity exists.
     *
     * @param  string  $id
     * @return bool
     */
    public function exists($id)
    {
        try {
            $this->find($id);
        } catch (ModelNotFoundException $e) {
            return false;
        }
        return true;
    }

    /**
     * Get number of items in storage.
     * @return int
     */
    public function count()
    {
        return $this->model->count();
    }

    /**
     * @return BaseModel
     * @throws RepositoryException
     */
    public function getNewModel()
    {
        $model = $this->model->newInstance();



        return $model;
    }

    /**
     * Return pk name
     * @return mixed
     */
    public function getKeyName()
    {
        return $this->model->getKeyName();
    }


    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->connectionResolver->connection($this->connectionName);
    }

    /**
     * @return string
     */
    protected function getModelClassName()
    {
        if (is_null($this->modelClassName)) {
            $this->modelClassName = get_class($this->model);
        }

        return $this->modelClassName;
    }

    /**
     * Model name.
     *
     * @return BaseModel
     */
    abstract protected function model();
}
