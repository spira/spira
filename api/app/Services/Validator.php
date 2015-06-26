<?php namespace App\Services;

use Illuminate\Container\Container as App;
use Illuminate\Support\MessageBag;
use App\Exceptions\ValidationException;

abstract class Validator
{
    /**
     * The application instance.
     *
     * @var Illuminate\Container\Container
     */
    protected $app;

    /**
     * Validator.
     *
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * Data to validate.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Assign dependencies.
     *
     * @param  Illuminate\Container\Container  $app
     * @return  void
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->validator = $this->app->make('validator');

        $this->registerValidateFloat();
        $this->registerValidateUuid();
    }

    /**
     * Model being validated.
     *
     * @var string
     */
    abstract protected function model();

    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Set custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'float' => 'The :attribute must be a float.',
            'uuid' => 'The :attribute must be an UUID string.'
        ];
    }

    /**
     * Add data to validate against.
     *
     * @param  array  $data
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Add id to the data array to validate.
     *
     * @param  string  $id
     * @return $this
     */
    public function id($id)
    {
        // If the data already has an ID set, don't allow to override it, but
        // instead thrown a validation exception.
        if (array_key_exists($this->getKey(), $this->data)) {

            $error = new MessageBag;

            throw new ValidationException(
                $error->add($this->getKey(), 'The existing ID should not be overwritten.')
            );
        }

        $this->data[$this->getKey()] = $id;

        return $this;
    }

    /**
     * Validate the current data.
     *
     * @throws \Illuminate\Http\Exception\HttpResponseException
     * @return void
     */
    public function validate()
    {
        $method = strtolower($this->app->request->method());
        if (method_exists($this, $method)) {
            $this->{$method}();
        }

        if (!$this->passes()) {

            throw new ValidationException($this->errors);
        }
    }

    /**
     * Test if validation passes.
     *
     * @return bool
     */
    public function passes()
    {
        $validator = $this->validator->make(
            $this->data,
            $this->rules(),
            $this->messages()
        );

        if ($validator->fails()) {
            $this->errors = $validator->messages();

            return false;
        }

        return true;
    }

    /**
     * Modify the rules for put operations.
     *
     * @return $this
     */
    public function put()
    {
        $this->rules = array_add($this->rules, $this->getKey(), 'required|uuid');

        return $this;
    }

    /**
     * Modify the rules for patch operations.
     *
     * @return $this
     */
    public function patch()
    {
        $this->rules = array_only($this->rules, array_keys($this->data));
        $this->rules = array_add($this->rules, $this->getKey(), 'required|uuid|exists:'.$this->getTable().','.$this->getKey());

        return $this;
    }

    /**
     * Modify the rules for delete operations.
     *
     * @return $this
     */
    public function delete()
    {
        $this->rules = [$this->getKey() => 'required|uuid|exists:'.$this->getTable().','.$this->getKey()];

        return $this;
    }

    /**
     * Retrieve validation errors.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Get the table name for the model being validated.
     *
     * @return string
     */
    protected function getTable()
    {
        $model = $this->model();
        return with(new $model())->getTable();
    }

    /**
     * Get the primary key name for the model being validated.
     *
     * @return string
     */
    protected function getKey()
    {
        $model = $this->model();
        return with(new $model())->getKeyName();
    }

    /**
     * Register custom validation rule for float.
     *
     * @return void
     */
    protected function registerValidateFloat()
    {
        $this->validator->extend('float', function($attribute, $value, $parameters)
        {
            return is_float($value + 0);
        });
    }

    /**
     * Register custom validation rule for UUID strings.
     *
     * @return void
     */
    protected function registerValidateUuid()
    {
        $this->validator->extend('uuid', function($attribute, $value, $parameters)
        {
            return preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/', $value);
        });
    }
}
