<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 15:07
 */

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class Tag extends BaseModel
{
    public $table = 'tags';

    protected $primaryKey = 'tag_id';

    public $incrementing = true;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tag'];

    protected static $validationRules = [
            'tag' => 'required|string|alphaDashSpace'
        ];

    /**
     * Provide a UUID
     *
     * @param $model
     * @return bool|void
     */
    protected function provideUuidKey($model) {
        return true;
    }

    /**
     * @param mixed $id
     * @return BaseModel
     * @throws ModelNotFoundException
     */
    public function findByIdentifier($id)
    {
        try{
            return $this->where('tag','=',$id)->firstOrFail();
        }catch (ModelNotFoundException $e){
            return $this->findOrFail($id);
        }
    }
}
