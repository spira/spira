<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Bosnadev\Database\Traits\UuidTrait;

abstract class BaseModel extends Model
{
    use UuidTrait;

    public $incrementing = false;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['self'];

    /**
     * Get the access route for the entity.
     *
     * @return string
     */
    abstract public function entityRoute();

    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getSelfAttribute()
    {
        return url($this->entityRoute().'/'.$this->{$this->primaryKey});
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        // Run the parent cast rules in the parent method
        $value = parent::castAttribute($key, $value);

        if (is_null($value)) return $value;

        switch ($this->getCastType($key))
        {
            case 'date':
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i', $value.' 00:00')->toIso8601String();
            case 'datetime':
                return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value)->toIso8601String();
            default:
                return $value;
        }
    }
}
