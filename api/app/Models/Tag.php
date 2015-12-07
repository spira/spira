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
use Spira\Core\Model\Model\IndexedModel;

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
    protected $fillable = ['tag_id', 'tag', 'searchable'];

    protected $touches = ['articles'];

    protected static $validationRules = [
        'tag_id' => 'required|uuid',
        'tag' => 'required|string|max:30',
    ];

    protected $mappingProperties = [
        'tag_id' => [
            'type' => 'string',
            'index' => 'no',
        ],
        'tag' => [
            'type' => 'string',
            'index_analyzer' => 'autocomplete',
            'search_analyzer' => 'standard',
        ],
    ];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'post_tag', 'tag_id', 'post_id', 'articles')->withPivot('tag_group_id', 'tag_group_parent_id');
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
        if (! $tagDefinition) {
            $tagDefinition = SeedTags::$tagHierarchy[$parentTagName];
        }

        array_walk($tagDefinition['children'], function (&$value, $key) {
            if (is_string($key)) {
                $value = $key;
            }
        });

        $tagGroupNames = array_values($tagDefinition['children']);

        $groupTagIds = self::whereIn('tag', $tagGroupNames)
            ->lists('tag_id');

        $parentTagId = self::where('tag', '=', $parentTagName)->value('tag_id');

        $syncTags = $tags->map(function (Tag $tag) use ($parentTagId, $groupTagIds) {
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
                'linked_tags_limit',
                'read_only',
            ]);
    }

    public function parentTags()
    {
        return $this->belongsToMany(self::class, 'tag_tag', 'tag_id', 'parent_tag_id')
            ->withPivot([
                'required',
                'linked_tags_must_exist',
                'linked_tags_must_be_children',
                'linked_tags_limit',
                'read_only',
            ]);
    }

    protected function getBelongsRelation($related, $relation)
    {
        return $this->belongsToMany($related, null, null, null, $relation)->withPivot('tag_group_id', 'tag_group_parent_id');
    }
}
