<?php namespace App\Models;

use Carbon\Carbon;
use Bosnadev\Database\Traits\UuidTrait;
use DateTime;
use Illuminate\Validation\Factory as ValidationFactory;

abstract class BaseModel extends \Spira\Repository\Model\BaseModel
{
    use UuidTrait;

    public $incrementing = false;

    /**
     * @return ValidationFactory
     */
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
