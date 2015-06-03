<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public $incrementing = false;

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    /**
     * Accessor to get created_at as an ISO 8601 string.
     *
     * @param  string  $date
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->toIso8601String();
    }

    /**
     * Accessor to get updated_at as an ISO 8601 string.
     *
     * @param  string  $date
     * @return string
     */
    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $date)->toIso8601String();
    }
}
