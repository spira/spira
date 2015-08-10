<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 13.07.15
 * Time: 15:07
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class SecondTestEntity extends ChildBaseModel
{
    public $table = 'second_test_entities';

    protected $primaryKey = 'entity_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['entity_id', 'check_entity_id', 'value'];

    protected $validationRules = [
            'entity_id' => 'uuid|createOnly',
            'check_entity_id' => 'uuid',
            'value' => 'required|string'
        ];

    /**
     * @param Builder $query
     * @param BaseModel $parent
     * @return Builder
     */
    protected function attachParentModelToQuery(Builder $query, BaseModel $parent)
    {
        return $query->where('check_entity_id','=', $parent->entity_id);
    }

    /**
     * @param BaseModel $parent
     * @return void
     */
    public function attachParent(BaseModel $parent)
    {
        $this->check_entity_id = $parent->entity_id;
    }
}
