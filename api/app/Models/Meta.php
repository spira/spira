<?php


namespace App\Models;


use Illuminate\Support\Str;
use Spira\Model\Model\BaseModel;

abstract class Meta extends BaseModel
{
    protected $primaryKey = 'meta_id';

    protected $fillable = ['meta_id', 'meta_name', 'meta_content'];

    protected $touches = [];

    protected static function boot()
    {
        //auto touching
        static::booted(function (Meta $model) {
            $className = $model->getParentClassName();
            $model->fillable[] = $className::getPrimaryKey();
            $model->touches[] = Str::snake(class_basename($className));
            $touches = array_unique($model->touches);
            $model->setTouchedRelations($touches);
            return true;
        });

        parent::boot();
    }

    public static function getValidationRules()
    {
        return [
            'meta_id' => 'uuid',
            'meta_name' => 'required|string',
            'meta_content' => 'string',
        ];
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($method === $this->getMethodName()){
            return $this->belongsTo($this->getParentClassName(), null, null, $method);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * @return BaseModel
     */
    abstract public function getParentClassName();


    /**
     * @return string
     */
    protected function getMethodName()
    {
        return Str::snake(class_basename($this->getParentClassName()));
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if ($key === $this->getMethodName() || method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }
}