<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\User;
use App\Models\Image;
use Illuminate\Support\Str;
use App\Models\ArticleContentPiece;
use Spira\Model\Collection\Collection;
use App\Models\ArticleContent\ImageContent;
use App\Models\ArticleContentPiecesDisplay;
use App\Models\ArticleContent\RichTextContent;
use App\Models\ArticleContent\BlockquoteContent;


$factory->define(App\Models\Article::class, function (\Faker\Generator $faker) {

    /** @var Collection $users */
    static $users = null;

    if (is_null($users)) {
        $users = \App\Models\User::all();
    }

    return [
        'article_id' => $faker->uuid,
        'title' => $faker->sentence,
        'status' => $faker->randomElement(App\Models\Article::$statuses),
        'excerpt' => Str::words($faker->realText(100), 30, ''),
        'primary_image' => $faker->imageUrl(500, 500, 'food'),
        'permalink' => $faker->boolean(90) ? $faker->unique()->slug : null,
        'author_id' => $users->random(1)->user_id,
        'author_display' => $faker->boolean(50),
        'show_author_promo' => $faker->boolean(50),
        'first_published' => $faker->boolean(90) ? $faker->dateTimeThisDecade()->format('Y-m-d H:i:s') : null,
    ];
});

$factory->define(RichTextContent::class, function (\Faker\Generator $faker) {

    $faker->addProvider(new \App\Extensions\Faker\Provider\Markdown($faker));

    return [
        'body' => $faker->markdown(rand(3, 5)),
    ];

});

$factory->define(BlockquoteContent::class, function (\Faker\Generator $faker) {

    /** @var User $author */
    $author = User::all()->random();

    return [
        'body' => $faker->realText(),
        'author' => $author->getFullNameAttribute(),
    ];

});

$factory->define(ImageContent::class, function (\Faker\Generator $faker) {

    $images = Image::all()->random(rand(1, 5));
    if (!$images instanceof \Illuminate\Support\Collection){
        $images = new Collection([$images]);
    }

    return [
        'images' => array_map(function(Image $image) use ($faker){
            return [
                'image' => $image->toArray(),
                'caption' => $faker->optional()->sentence(),
                'transformations' => null,
            ];
        }, array_values($images->all())),
    ];

});

$factory->define(ArticleContentPiece::class, function (\Faker\Generator $faker) {

    $type = $faker->randomElement(ArticleContentPiece::getContentTypes());
    $className = null;

    switch($type){
        case RichTextContent::CONTENT_TYPE:
            $className = RichTextContent::class;
        break;
        case BlockquoteContent::CONTENT_TYPE:
            $className = BlockquoteContent::class;
        break;
        case ImageContent::CONTENT_TYPE:
            $className = ImageContent::class;
        break;
    }

    return [
        'type' => $type,
        'content' => factory($className)->make(),
    ];

});

$factory->define(ArticleContentPiecesDisplay::class, function (\Faker\Generator $faker) {
    return [
        'sort_order' => []
    ];
});

$factory->define(App\Models\ArticlePermalink::class, function (\Faker\Generator $faker) {
    return [
        'permalink' => $faker->unique()->slug,
    ];
});

$factory->define(App\Models\ArticleMeta::class, function (\Faker\Generator $faker) {
    return [
        'meta_id' => $faker->uuid,
        'meta_name' => $faker->boolean(50) ? $faker->randomElement(['name','description','keyword','canonical']) : $faker->word,
        'meta_content' => $faker->slug,
    ];
});

$factory->define(App\Models\ArticleComment::class, function (\Faker\Generator $faker) {
    return [
        'article_comment_id' => $faker->unique()->randomNumber,
        'body' => $faker->paragraph,
        'created_at' => $faker->dateTime,
    ];
});

$factory->define(App\Models\ArticleImage::class, function (\Faker\Generator $faker) {
    return [
        'article_image_id' => $faker->uuid,
        'image_type' => $imageType = $faker->optional()->randomElement(['primary','thumbnail','carousel']),
        'position' => ($imageType == 'carousel') ? $faker->numberBetween(1, 10) : null,
        'alt' => $faker->optional()->sentence,
        'title' => $faker->optional()->sentence,
    ];
});
