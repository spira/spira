<?php namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseRepository
{
    /**
     * The application instance.
     *
     * @var Illuminate\Container\Container
     */
    protected $app;

    /**
     * Eloquent Model
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Assign dependencies.
     *
     * @param  Illuminate\Container\Container  $app
     * @return void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Model name.
     *
     * @return string
     */
    abstract protected function model();

    /**
     * Get an entity by id.
     *
     * @param  string  $id
     * @param  array   $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Get all entities.
     *
     * @param  array  $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return $this->model->get($columns);
    }

    /**
     * Create and store a new instance of an entity.
     *
     * @param  array  $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Create and store a collection of new entities.
     *
     * @param  array  $models
     * @return void
     */
    public function createMany(array $models)
    {
        foreach ($models as $model) {
            $this->model->create($model);
        }
    }

    /**
     * Create or replace an entity by id.
     *
     * @param  string  $id
     * @param  array   $data
     * @return mixed
     */
    public function createOrReplace($id, array $data)
    {
        try {

            $model = $this->find($id);

        } catch (ModelNotFoundException $e) {

            return $this->create(array_add($data, $this->model->getKeyName(), $id));
        }

        foreach ($model->getAttributes() as $key => $value) {
            if($value !== 0) {
                if (!in_array($key, ['created_at', 'updated_at'])) {
                    $model->{$key} = null;
                }
            }
        }

        return $model->update(array_add($data, $this->model->getKeyName(), $id));
    }

    /**
     * Create or replace a colleciton of entities.
     *
     * @param  array  $entities
     * @return void
     */
    public function createOrReplaceMany(array $entities)
    {
        foreach ($entities as $entity) {
            $id = array_pull($entity, $this->model->getKeyName());

            $this->put($id, $entity);
        }
    }

    /**
     * Update an entity by id.
     *
     * @param  string  $id
     * @param  array   $data
     * @return mixed
     */
    public function update($id, array $data)
    {
        $model = $this->find($id);

        return $model->update($data);
    }

    /**
     * Update a collection of entities.
     *
     * @param  array  $entities
     * @return void
     */
    public function updateMany(array $entities)
    {
        foreach ($entities as $entity) {
            $id = array_pull($entity, $this->model->getKeyName());

            $this->patch($id, $entity);
        }
    }

    /**
     * Delete an entity by id.
     *
     * @param  string  $id
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Delete a collection of entities by their ids.
     *
     * @param  array  $ids
     * @return mixed
     */
    public function deleteMany(array $ids)
    {
        return $this->model->destroy($ids);
    }

    /**
     * Get number of items in storage.
     *
     * @return int
     */
    public function count()
    {
        return $this->model->count();
    }

    /**
     * Get an instance of the model for the repository.
     *
     * @throws Exception
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }
}
