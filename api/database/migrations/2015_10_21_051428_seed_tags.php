<?php


use App\Models\Tag;
use Faker\Factory as Faker;
use Illuminate\Database\Migrations\Migration;

class SeedTags extends Migration
{

    /** @var \Faker\Generator */
    private $faker;

    // Group Tags
    const topicTagName = 'Topic';
    const categoryTagName = 'Category';
    const generalTagName = 'General';

    // Super Parent Tags
    const articleGroupTagName = 'Article Group';

    public static $defaultTagDefinition = [
        'searchable' => true,
        'pivot' => [
            'required' => false,
            'linked_tags_must_exist' => true,
            'linked_tags_must_be_children' => false,
            'linked_tags_limit' => null,
        ],
    ];

    public static $tagHierarchy = [
        self::articleGroupTagName => [
            'searchable' => false,
            'children' => [
                self::topicTagName => [
                    'searchable' => false,
                ],
                self::categoryTagName => [
                    'searchable' => false,
                ],
                self::generalTagName => [
                    'searchable' => false,
                    'pivot' => [
                        'linked_tags_must_exist' => false,
                    ],
                ],
            ],
        ],
    ];


    private function getTagInserts($tagDefinitionGroup, $parentTag = null, $existingInserts = [])
    {
        $tagInserts = [];
        $tagRelationshipInserts = [];

        if (!$this->faker){
            $this->faker = Faker::create();
        }

        foreach($tagDefinitionGroup as $tagName => $tagDefinition){

            if (is_string($tagDefinition)){
                $tagName = $tagDefinition;
                $tagDefinition = [];
            }

            $tagDefinition = array_replace_recursive(self::$defaultTagDefinition, $tagDefinition); //apply defaults

            $tagData = null;
            if(isset($existingInserts[$tagName])) {
                $tagData = $existingInserts[$tagName];
            }else{

                $tagData = [
                    'tag_id' => $this->faker->uuid(),
                    'tag' => $tagName,
                    'searchable' => $tagDefinition['searchable'],
                ];

                $tagInserts[$tagName] = $tagData;
            }

            if($parentTag){

                $tagRelationshipInserts[] = array_merge(
                    $tagDefinition['pivot'],
                    [
                        'tag_id' => $tagData['tag_id'],
                        'parent_tag_id' => $parentTag['tag_id'],
                    ]
                );
            }

            if(isset($tagDefinition['children'])){

                $childTagInserts = $this->getTagInserts($tagDefinition['children'], $tagData, $tagInserts);

                $tagInserts = array_merge($childTagInserts['tag_inserts'], $tagInserts);
                $tagRelationshipInserts = array_merge($childTagInserts['tag_relationship_inserts'], $tagRelationshipInserts);
            }
        }

        return [
            'tag_inserts' => $tagInserts,
            'tag_relationship_inserts' => $tagRelationshipInserts,
        ];
    }

    private function getTagNames($tagDefinitionGroup)
    {
        $names = [];
        foreach($tagDefinitionGroup as $tagName => $tagDefinition){
            if (is_string($tagDefinition)){
                $tagName = $tagDefinition;
                $tagDefinition = [];
            }

            $names[] = $tagName;

            if(isset($tagDefinition['children'])){
                array_push($names, $this->getTagNames($tagDefinition['children']));
            }
        }

        return $names;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tagInserts = $this->getTagInserts(self::$tagHierarchy);

        DB::table(Tag::getTableName())->insert($tagInserts['tag_inserts']);

        DB::table(CreateTagTagTable::TABLE_NAME)->insert($tagInserts['tag_relationship_inserts']);

        Tag::reindex();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(!Schema::hasTable(Tag::getTableName())){
            return;
        }

        $tagNames = array_keys($this->getTagInserts(self::$tagHierarchy)['tag_inserts']);

        DB::table(Tag::getTableName())->whereIn('tag', $tagNames)->delete();
        //no need to write rollback migration for the relationship table as it will cascade from the above query
    }
}
