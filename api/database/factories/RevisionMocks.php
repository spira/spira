<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$factory->define(Venturecraft\Revisionable\Revision::class, function (\Faker\Generator $faker) {
    return [
        'revision_id' => $faker->uuid,
        'revisionable_type' => $faker->word,
        'revisionable_id' => $faker->uuid,
        'user_id' => $faker->uuid,
        'key' => $faker->word,
        'old_value' => $faker->word,
        'new_value' => $faker->word,
        'created_at' => $faker->dateTime,
    ];
});

$factory->defineAs(Venturecraft\Revisionable\Revision::class, 'article', function ($faker) use ($factory) {
    $revision = $factory->raw(Venturecraft\Revisionable\Revision::class);

    // Get 2 articles to simulate an update
    $articles = [];
    $articles[0] = array_except($factory->raw(App\Models\Article::class), ['article_id']);
    $articles[1] = array_except($factory->raw(App\Models\Article::class), ['article_id']);

    // Inject child entities
    foreach ($articles as &$article) {
        $tags = factory(App\Models\Tag::class, $faker->numberBetween(2, 4))->make();
        $article['tags'] = $tags->lists('tag')->toArray();

        $meta = $factory->raw(App\Models\Meta::class);
        $article['metas'] = $meta;
    }

    // Pick a key to update
    $key = array_keys($articles[0])[array_rand(array_keys($articles[0]))];
    $articleRevision = [
        'revisionable_type' => 'App\Models\Article',
        'key' => $key,
        'old_value' => $articles[0][$key],
        'new_value' => $articles[1][$key],
    ];

    return array_merge($revision, $articleRevision);
});
