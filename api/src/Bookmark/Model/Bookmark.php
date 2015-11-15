<?php


namespace Spira\Bookmark\Model;

use Spira\Model\Model\BaseModel;

class Bookmark extends BaseModel
{
    public $table = 'bookmarks';

    protected $primaryKey = 'bookmark_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id'];

    protected static $validationRules = [
        'user_id' => 'required|uuid|exists:users,user_id',
    ];

    public function bookmarkable()
    {
        return $this->morphTo();
    }
}