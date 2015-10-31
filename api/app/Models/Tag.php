<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rhumsaa\Uuid\Uuid;
use SeedTags;
use Spira\Model\Model\BaseModel;
use Spira\Model\Model\IndexedModel;

class Tag extends IndexedModel
{
    public $table = 'tags';

    protected $primaryKey = 'tag_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tag_id','tag', 'searchable'];

    protected static $validationRules = [
        'tag_id' => 'required|uuid',
        'tag' => 'required|string|max:30',
    ];

    protected $mappingProperties = [
        'tag_id' => [
            'type' => 'string',
            'index' => 'no'
        ],
        'tag' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard'
        ]
    ];

    protected $taggedModels = [
        'articles' => Article::class,
    ];

    protected static function boot()
    {
        //auto touching
        static::booted(function (Tag $model) {
            $touches = array_merge($model->touches, array_keys($model->taggedModels));
            $touches = array_unique($touches);
            $model->setTouchedRelations($touches);
            return true;
        });

        parent::boot();
    }

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

    /**
     * @param Collection $tags
     * @param $parentTagName
     * @param array $tagDefinition
     * @return Collection
     */
    public static function getGroupedTagPivots(Collection $tags, $parentTagName, array $tagDefinition = null)
    {

        if (!$tagDefinition){
            $tagDefinition = SeedTags::$tagHierarchy[$parentTagName];
        }

        array_walk($tagDefinition['children'], function(&$value, $key){
            if(is_string($key)){
                $value = $key;
            }
        });

        $tagGroupNames = array_values($tagDefinition['children']);

        $groupTagIds = Tag::whereIn('tag', $tagGroupNames)
            ->lists('tag_id');

        $parentTagId = Tag::where('tag', '=', $parentTagName)->value('tag_id');

        $syncTags = $tags->map(function(Tag $tag) use ($parentTagId, $groupTagIds){
            return [
                'tag_group_id' => $groupTagIds->random(),
                'tag_group_parent_id' => $parentTagId,
                'tag_id' => $tag->getKey(),
            ];
        })->keyBy('tag_id');

        return $syncTags;
    }

    public function childTags()
    {
        return $this->belongsToMany(self::class, 'tag_tag', 'parent_tag_id', 'tag_id')
            ->withPivot([
                'required',
                'linked_tags_must_exist',
                'linked_tags_must_be_children',
                'linked_tags_limit'
            ]);
    }

    public function parentTags()
    {
        return $this->belongsToMany(self::class, 'tag_tag', 'tag_id', 'parent_tag_id')
            ->withPivot([
                'required',
                'linked_tags_must_exist',
                'linked_tags_must_be_children',
                'linked_tags_limit'
            ]);
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
        if (isset($this->taggedModels[$key]) || method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
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
        if (isset($this->taggedModels[$method])){
            return $this->belongsToMany($this->taggedModels[$method],null, null, null, $method)->withPivot('tag_group_id', 'tag_group_parent_id');
        }

        return parent::__call($method, $parameters);
    }
}
