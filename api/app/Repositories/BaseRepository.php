<?php

namespace App\Repositories;

use App\Exceptions\FatalErrorException;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
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
     * Eloquent Model.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Assign dependencies.
     *
     * @param Illuminate\Container\Container $app
     *
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
     * @param string $id
     * @param array  $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Get all entities.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * Create and store a new instance of an entity.
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        $entity = $this->model->create($data);

        return [$entity->self];
    }

    /**
     * Create and store a collection of new entities.
     *
     * @param array $models
     *
     * @return void
     */
    public function createMany(array $models)
    {
        $this->app->db->beginTransaction();

        foreach ($models as $model) {
            $this->model->create($model);
        }

        $this->app->db->commit();
    }

    /**
     * Create or replace an entity by id.
     *
     * @param string $id
     * @param array  $data
     *
     * @throws App\Exceptions\FatalException
     *
     * @return array
     */
    public function createOrReplace($id, array $data)
    {
        $keyName = $this->model->getKeyName();

        // Make sure the data does not contain a different id for the entity.
        if (array_key_exists($keyName, $data) and $id !== $data[$keyName]) {
            throw new FatalErrorException('Attempt to override entity ID value.');
        }

        try {
            $model = $this->find($id);
        } catch (ModelNotFoundException $e) {
            $link = $this->create(array_add($data, $keyName, $id));

            return [$link[0]];
        }

        foreach ($model->getAttributes() as $key => $value) {
            if ($value !== 0) {
                if (!in_array($key, ['created_at', 'updated_at'])) {
                    $model->{$key} = null;
                }
            }
        }

        $model->update(array_add($data, $keyName, $id));

        return [$model->self];
    }

    /**
     * Create or replace a colleciton of entities.
     *
     * @param array $entities
     *
     * @return array
     */
    public function createOrReplaceMany(array $entities)
    {
        $this->app->db->beginTransaction();

        $links = [];

        foreach ($entities as $entity) {
            $id = array_pull($entity, $this->model->getKeyName());

            $link = $this->createOrReplace($id, $entity);
            array_push($links, $link[0]);
        }

        $this->app->db->commit();

        return $links;
    }

    /**
     * Update an entity by id.
     *
     * @param string $id
     * @param array  $data
     *
     * @throws App\Exceptions\FatalException
     *
     * @return mixed
     */
    public function update($id, array $data)
    {
        // Make sure the data does not contain a different id for the entity.
        $keyName = $this->model->getKeyName();
        if (array_key_exists($keyName, $data) and $id !== $data[$keyName]) {
            throw new FatalErrorException('Attempt to override entity ID value.');
        }

        $model = $this->find($id);

        return $model->update($data);
    }

    /**
     * Update a collection of entities.
     *
     * @param array $entities
     *
     * @return void
     */
    public function updateMany(array $entities)
    {
        $this->app->db->beginTransaction();

        foreach ($entities as $entity) {
            $id = array_pull($entity, $this->model->getKeyName());

            $this->update($id, $entity);
        }

        $this->app->db->commit();
    }

    /**
     * Delete an entity by id.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Delete a collection of entities by their ids.
     *
     * @param array $ids
     *
     * @return void
     */
    public function deleteMany(array $ids)
    {
        $this->app->db->beginTransaction();

        $this->model->destroy($ids);

        $this->app->db->commit();
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
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }
}
