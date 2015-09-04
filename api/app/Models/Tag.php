<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rhumsaa\Uuid\Uuid;
use Spira\Model\Model\BaseModel;

class Tag extends BaseModel
{
    public $table = 'tags';

    protected $primaryKey = 'tag_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tag_id','tag'];

    protected static $validationRules = [
        'tag_id' => 'required|uuid',
        'tag' => 'required|string|alphaDashSpace|max:20',
    ];

    /**
     * @param mixed $id
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        //if the id is a uuid, try that or fail.
        if (Uuid::isValid($id)) {
            return parent::findOrFail($id);
        }

        return $this->where('tag', '=', $id)->firstOrFail();
    }
}
