<?php namespace App\Models;

use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;

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
     * Generate fake user
     * @param array $overrides
     * @param null $seed
     * @return TestEntity
     */
    public static function fakeTestEntity($overrides = [], $seed = null){

        $faker = Faker::create('au_AU');;

        if ($seed){
            $faker->seed($seed);
        }

        $testEntityInfo = array_merge([

            'entity_id' => $faker->uuid,
            'varchar' => $faker->word,
            'hash' => Hash::make($faker->randomDigitNotNull),
            'integer' => $faker->numberBetween(0, 500),
            'decimal' => $faker->randomFloat(2, 0, 100),
            'boolean' => $faker->boolean(),
            'nullable' => $faker->optional(0.5)->boolean(),
            'text' => $faker->paragraph(3),
            'date' => $faker->date(),
            'multi_word_column_title' => true,
            'hidden' => $faker->boolean()

        ], $overrides);

        $testEntity = new TestEntity($testEntityInfo);

        $testEntity->timestamps = true;
        $testEntity->save();

        return $testEntity;

    }

}