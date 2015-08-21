<?php namespace App\Models;

use Carbon\Carbon;
use Bosnadev\Database\Traits\UuidTrait;
use DateTime;

abstract class BaseModel extends \Spira\Model\Model\BaseModel
{
    use UuidTrait;

    public $incrementing = false;

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
                if (is_array($value)){
                    return $this->asDateTime($value);
                }
                return Carbon::createFromFormat('Y-m-d', $value);
            case 'datetime':
                if (is_array($value)){
                    return $this->asDateTime($value);
                }
                return Carbon::createFromFormat('Y-m-d H:i:s', $value);
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
