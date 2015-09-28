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
use App\Models\Section;
use App\Models\Sections\ImageContent;
use App\Models\ArticleSectionsDisplay;
use Spira\Model\Collection\Collection;
use App\Models\Sections\RichTextContent;
use App\Models\Sections\BlockquoteContent;

$factory->define(Section::class, function (\Faker\Generator $faker) {

    $type = $faker->randomElement(Section::getContentTypes());
    $className = null;

    switch ($type) {
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
        'section_id' => $faker->uuid,
        'type' => $type,
        'content' => factory($className)->make(),
    ];

});

$factory->define(ArticleSectionsDisplay::class, function (\Faker\Generator $faker) {
    return [
        'sort_order' => [],
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

    if ($faker->boolean()) {
        $images = new Collection([Image::all()->random()]);
    } else {
        $images = Image::all()->random(rand(2, 5));
    }

    return [
        'images' => array_map(function (Image $image) use ($faker) {
            return [
                '_image' => $image->toArray(),
                'caption' => $faker->optional()->sentence(),
                'transformations' => null,
            ];
        }, array_values($images->all())),
    ];

});

