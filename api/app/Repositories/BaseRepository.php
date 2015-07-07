<?php namespace App\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository
{
    /**
     * Eloquent Model
     *
     * @var Model
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
     * @param ConnectionResolverInterface $connectionResolver
     * @throws \Exception
     */
    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        $this->model = $this->getModel();
        $this->connectionResolver = $connectionResolver;
    }


    /**
     * Get an entity by id.
     *
     * @param  string  $id
     * @param  array   $columns
     * @return Model
     * @throws ModelNotFoundException
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Get all entities.
     *
     * @param  array  $columns
     * @return Collection
     */
    public function all($columns = array('*'))
    {
        return $this->model->get($columns);
    }

    /**
     * @param Model $model
     * @return bool|Model
     * @throws RepositoryException
     */
    public function save(Model $model)
    {
        $modelClassName = $this->getModelClassName();
        if (!($model instanceof $modelClassName)){
            throw new RepositoryException('provided model is not instance of '.$modelClassName);
        }
        /** @var Model $model */

        if ($model->save()){
            return $model;
        }

        return false;
    }

    /**
     * @param Model[] $models
     * @return Model[]
     * @throws \Exception some general exception
     * @throws RepositoryException
     */
    public function saveMany(array $models)
    {
        $this->getConnection()->beginTransaction();

        try{
            foreach ($models as $model)
            {
                if (!$this->save($model)){
                    throw new RepositoryException('Massive assignment failed as model with id '.$model->getQueueableId().' couldn\'t be saved');
                }
            }
        }catch (\Exception $e){
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return $models;
    }


    /**
     * Delete an entity by id.
     *
     * @param Model $model
     * @return bool
     * @throws RepositoryException
     */
    public function delete(Model $model)
    {
        $modelClassName = $this->getModelClassName();
        if (!($model instanceof $modelClassName)){
            throw new RepositoryException('provided model is not instance of '.$modelClassName);
        }

        /** @var Model $model */

        return $model->delete();
    }

    /**
     * Delete a collection of entities.
     *
     * @param  Model[] $models
     * @throws \Exception
     * @return bool
     */
    public function deleteMany(array $models)
    {
        $this->getConnection()->beginTransaction();

        try{
            foreach ($models as $model)
            {
                if (!$this->delete($model)){
                    throw new RepositoryException('Massive deletion failed as model with id '.$model->getQueueableId().' couldn\'t be deleted');
                }
            }
        }catch (\Exception $e){
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
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
     * Get new model instance.
     * @return Model
     * @throws \Exception
     */
    public function getModel()
    {
        $model = $this->model();

        if (!$model instanceof Model){
            throw new RepositoryException("Class {$this->getModelClassName()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
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
    private function getModelClassName()
    {
        if (is_null($this->modelClassName)){
            $this->modelClassName = get_class($this->model);
        }

        return $this->modelClassName;
    }

    /**
     * Model name.
     *
     * @return Model
     */
    abstract protected function model();
}
