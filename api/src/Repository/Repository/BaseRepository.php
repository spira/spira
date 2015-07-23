<?php namespace Spira\Repository\Repository;

use App\Models\BaseModel;
use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spira\Repository\Collection\Collection;
use Spira\Repository\Specification\EloquentSpecificationInterface;

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
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * @param EloquentSpecificationInterface $specification
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function findSpecifying(EloquentSpecificationInterface $specification, $columns = ['*'])
    {
        $qb = $this->model->query();
        $specification->attachCriteriaToBuilder($qb);
        return $qb->get($columns);
    }

    /**
     * Find a model by its primary key.
     *
     * @param  array  $ids
     * @param  array  $columns
     * @return Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        return $this->model->findMany($ids, $columns);
    }

    /**
     * Get all entities.
     *
     * @param  array  $columns
     * @return Collection
     */
    public function all($columns = ['*'])
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
