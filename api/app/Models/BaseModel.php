<?php namespace App\Models;

use App\Exceptions\ValidationException;
use App\Services\SpiraValidator;
use Carbon\Carbon;
use Bosnadev\Database\Traits\UuidTrait;
use DateTime;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Factory as Validator;

abstract class BaseModel extends \Spira\Repository\Model\BaseModel
{
    use UuidTrait;

    public $incrementing = false;

    public $exceptionOnError = true;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var MessageBag|null
     */
    protected $errors;

    protected $validationRules = [];

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
    }

    protected function getValidator()
    {
        if (is_null($this->validator)) {
            $this->validator = \App::make('validator');
        }

        return $this->validator;
    }

    public static function getTableName()
    {
        return with(new static())->getTable();
    }

    /**
     * @return MessageBag|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        // Run the parent cast rules in the parent method
        $value = parent::castAttribute($key, $value);

        switch ($this->getCastType($key)) {
            case 'date':
                return Carbon::createFromFormat('Y-m-d', $value);
            case 'datetime':
                return Carbon::createFromFormat('Y-m-d H:i:s', $value);
            default:
                return $value;
        }
    }

    /**
     * @param array $options
     * @return bool|null
     * @throws \Exception
     */
    public function save(array $options = [])
    {
        $result = true;
        if ($this->fireModelEvent('validating') !== false) {
            $result = $this->validate();
        }

        if (!$result && $this->exceptionOnError) {
            throw new ValidationException($this->getErrors());
        }

        if ($this->fireModelEvent('validated') === false) {
            return false;
        }

        return parent::save($options);
    }

    public function validate()
    {
        /** @var SpiraValidator $validation */
        $validation = $this->getValidator()->make($this->attributes, $this->getValidationRules());
        $validation->setModel($this);
        $this->errors = [];
        if ($validation->fails()) {
            $this->errors = $validation->messages();
            return false;
        }

        return true;
    }

    /**
     * Register a validating model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     * @return void
     */
    public static function validating($callback, $priority = 0)
    {
        static::registerModelEvent('validating', $callback, $priority);
    }

    /**
     * Register a validated model event with the dispatcher.
     *
     * @param  \Closure|string  $callback
     * @param  int  $priority
     * @return void
     */
    public static function validated($callback, $priority = 0)
    {
        static::registerModelEvent('validated', $callback, $priority);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getDates()) && $value) {
            if (!$value instanceof Carbon && ! $value instanceof DateTime) {
                $value = new Carbon($value);
                $this->attributes[$key] = $value;
                return;
            }
        }

        parent::setAttribute($key, $value);
    }
}
