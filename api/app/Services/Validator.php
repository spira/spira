<?php

namespace app\Services;

use App\Exceptions\ValidationException;
use Illuminate\Container\Container as App;
use Illuminate\Support\MessageBag;

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
     * Validation error messages.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Validation failed rules.
     *
     * @var array
     */
    protected $failed = [];

    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Transformer to use for formatting.
     *
     * @var string
     */
    protected $transformer = 'App\Http\Transformers\BaseTransformer';

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
            'uuid'  => 'The :attribute must be an UUID string.',
        ];
    }

    /**
     * Add data to validate against.
     *
     * @param array $data
     *
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
     * @param string $id
     *
     * @throws App\Exceptions\ValidationException
     *
     * @return $this
     */
    public function id($id)
    {
        /*
         * If the entity id does not match the url id, the client is likely trying
         * to mistakenly overwrite the entity at the url with an incorrect entity.
         */
        if (array_key_exists($this->getKey(), $this->data) && $this->data[$this->getKey()] !== $id) {
            $this->errors = new MessageBag();
            $this->errors->add($this->getKey(), 'The url id does not match the json entity id.');
            $this->failed = [$this->getKey() => ['mismatch_id' => []]];

            throw new ValidationException($this->formattedErrors());
        }

        $this->data[$this->getKey()] = $id;

        return $this;
    }

    /**
     * Validate the current data.
     *
     * @throws App\Exceptions\ValidationException
     *
     * @return void
     */
    public function validate()
    {
        $method = strtolower($this->app->request->method());
        if (method_exists($this, $method)) {
            $this->{$method}();
        }

        if (!$this->passes()) {
            throw new ValidationException($this->formattedErrors());
        }
    }

    /**
     * Validates an array of entities.
     *
     * @throws App\Exceptions\ValidationException
     *
     * @return void
     */
    public function validateMany()
    {
        $entities = $this->data;
        $errors = [];

        foreach ($entities as $entity) {
            if (strtolower($this->app->request->method()) == 'delete') {
                $this->data = [];
                $this->id($entity);
            } else {
                $this->data = $entity;
            }

            try {
                $this->validate();
            } catch (ValidationException $e) {
                array_push($errors, $this->formattedErrors()->toArray());
                continue;
            }

            array_push($errors, null);
        }

        if (!empty(array_filter($errors))) {

            // Use merge instead of passing the errors to the MessageBag
            // constructor, to preserve nulls
            $errorBag = new MessageBag();
            $errorBag->merge($errors);

            throw new ValidationException($errorBag);
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
            $this->failed = $validator->failed();

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
     * Reformat and combined the failed and messages arrays.
     *
     * @return \Illuminate\Support\MessageBag
     */
    protected function formattedErrors()
    {
        $messages = $this->errors->toArray();
        $types = $this->failed;
        $formatted = [];

        foreach ($messages as $key => $value) {
            $combined = array_combine(array_keys($types[$key]), $value);

            $formatted[$key] = array_map(function ($key) use ($combined) {
                return [
                    'type'    => strtolower($key),
                    'message' => $combined[$key],
                ];
            }, array_keys($combined));
        }

        // Transform the messages with the current transformer
        $transformer = $this->app->make('App\Services\Transformer');
        $transformed = $transformer->item(
            new MessageBag($formatted),
            new $this->transformer()
        );

        return new MessageBag($transformed);
    }

    /**
     * Register custom validation rule for float.
     *
     * @return void
     */
    protected function registerValidateFloat()
    {
        $this->validator->extend('float', function ($attribute, $value, $parameters) {
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
        $this->validator->extend('uuid', function ($attribute, $value, $parameters) {
            return preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/', $value);
        });
    }
}
