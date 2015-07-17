<?php namespace App\Models;

use App\Exceptions\ValidationException;
use Carbon\Carbon;
use Bosnadev\Database\Traits\UuidTrait;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Factory as Validator;

abstract class BaseModel extends \Spira\Repository\Model\BaseModel
{
    use UuidTrait;

    public $incrementing = false;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var MessageBag|null
     */
    protected $errors;

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return [];
    }

    protected function getValidator()
    {
        if (is_null($this->validator)){
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

        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'date':
                return Carbon::createFromFormat('Y-m-d H:i', $value.' 00:00')->toIso8601String();
            case 'datetime':
                return Carbon::createFromFormat('Y-m-d H:i:s', $value)->toIso8601String();
            default:
                return $value;
        }
    }

    /**
     * Listen for save event
     */
    protected static function boot()
    {
        parent::boot();
        static::saving(function(BaseModel $model)
        {
            return $model->validate();
        });
    }

    public function validate()
    {
        $validation = $this->getValidator()->make($this->attributes,$this->getValidationRules());
        $this->errors = [];
        if ($validation->fails()){
            $this->errors = $validation->messages();
            throw new ValidationException($this->getErrors());
        }

        return true;
    }

}
