<?php namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;

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
     * Get all rows.
     *
     * @param  array  $columns
     * @return mixed
     */
    public function all($columns = array('*'))
    {
        return $this->model->get($columns);
    }

    /**
     * Create and store a new instance of the model.
     *
     * @param  array  $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Get model by id.
     *
     * @param  string  $id
     * @param  array  $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Get an instance of the model for the repository.
     *
     * @throws Exception
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }
}
