<?php namespace App\Models;

use Spira\Model\Model\BaseModel;

class History extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'history';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'history_id',
        'historable_id',
        'historable_type',
        'parent_id',
        'user_id',
        'changes',
        'created_at'
    ];

    /**
     * Defines polymorphic relationship with any model that tracks history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function historable()
    {
        return $this->morphTo();
    }
}
