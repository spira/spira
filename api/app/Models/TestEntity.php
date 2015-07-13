<?php namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class TestEntity
 *
 * @property Collection $testMany
 */
class TestEntity extends BaseModel {
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'test_entities';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['entity_id', 'varchar', 'hash', 'integer', 'decimal', 'boolean', 'nullable', 'text', 'date', 'multi_word_column_title', 'hidden'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['hidden'];

    protected $primaryKey = 'entity_id';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'decimal' => 'float',
        'date' => 'date',
        'created_at' => 'dateTime',
        'updated_at' => 'dateTime'
    ];

    public function addSecondTest(SecondTestEntity $entity)
    {
        $this->testMany->add($entity);
        return $this;
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return [
            'varchar' => 'required|string',
            'hash'    => 'required|string',
            'integer' => 'required|integer',
            'decimal' => 'required',
            'boolean' => 'required|boolean',
            'text'    => 'required|string',
            'date'    => 'required|date',
            'multi_word_column_title' => 'required|boolean',
            'hidden'  => 'required|boolean'
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function testMany()
    {
        return $this->belongsToMany('App\Models\SecondTestEntity', 'test_many_many', 'test_id', 'test_second_id');
    }
}