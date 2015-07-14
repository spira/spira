<?php namespace App\Repositories;

use App\Models\BaseModel;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Traversable;

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
     * @return BaseModel
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
     * @param BaseModel $model
     * @return BaseModel|false
     * @throws RepositoryException
     * @throws \Exception
     */
    public function save(BaseModel $model)
    {
        $modelClassName = $this->getModelClassName();
        if (!($model instanceof $modelClassName)){
            throw new RepositoryException('provided model is not instance of '.$modelClassName);
        }
        /** @var Model $model */
        $this->getConnection()->beginTransaction();

        try{
            if (!$model->push()){
                throw new RepositoryException('couldn\'t save model');
            }
        }catch (\Exception $e){
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();
        return $model;
    }

    /**
     * @param Model[] $models
     * @return Model[]
     * @throws \Exception some general exception
     * @throws RepositoryException
     */
    public function saveMany($models)
    {
        if (!is_array($models) && !($models instanceof Traversable)){
            throw new RepositoryException('Models must be either an array or Collection with Traversable');
        }

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
     * @param BaseModel $model
     * @return bool
     * @throws RepositoryException
     */
    public function delete(BaseModel $model)
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
     * @param  BaseModel[] $models
     * @throws \Exception
     * @return bool
     */
    public function deleteMany($models)
    {
        if (!is_array($models) && !($models instanceof Traversable)){
            throw new RepositoryException('Models must be either an array or Collection with Traversable');
        }

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
     * @return BaseModel
     * @throws RepositoryException
     */
    public function getModel()
    {
        $model = $this->model();

        if (!$model instanceof BaseModel){
            throw new RepositoryException("Class {$this->getModelClassName()} must be an instance of ".BaseModel::class);
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
     * @return BaseModel
     */
    abstract protected function model();
}
