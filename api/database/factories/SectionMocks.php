<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Faker\Generator;
use App\Models\User;
use App\Models\Image;
use App\Models\Section;
use App\Models\Sections\ImageContent;
use App\Models\ArticleSectionsDisplay;
use Spira\Model\Collection\Collection;
use App\Models\Sections\RichTextContent;
use App\Models\Sections\BlockquoteContent;


$factory->define(RichTextContent::class, function (Generator $faker) {

    $faker->addProvider(new \App\Extensions\Faker\Provider\Markdown($faker));

    return [
        'body' => $faker->markdown(rand(3, 5)),
    ];

});

$factory->define(BlockquoteContent::class, function (Generator $faker) {

    /** @var User $author */
    $author = User::all()->random();

    return [
        'body' => $faker->realText(),
        'author' => $author->getFullNameAttribute(),
    ];

});

$factory->define(ImageContent::class, function (Generator $faker) {

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

$factory->defineAs(Section::class, RichTextContent::CONTENT_TYPE, function (Generator $faker) {

    return [
        'section_id' => $faker->uuid,
        'type' => RichTextContent::CONTENT_TYPE,
        'content' => factory(RichTextContent::class)->make(),
    ];

});

$factory->defineAs(Section::class, BlockquoteContent::CONTENT_TYPE, function (Generator $faker) {

    return [
        'section_id' => $faker->uuid,
        'type' => BlockquoteContent::CONTENT_TYPE,
        'content' => factory(BlockquoteContent::class)->make(),
    ];

});

$factory->defineAs(Section::class, ImageContent::CONTENT_TYPE, function (Generator $faker) {

    return [
        'section_id' => $faker->uuid,
        'type' => ImageContent::CONTENT_TYPE,
        'content' => factory(ImageContent::class)->make(),
    ];

});

$factory->define(ArticleSectionsDisplay::class, function (Generator $faker) {
    return [
        'sort_order' => [],
    ];
});

$factory->define(Section::class, function (Generator $faker) use ($factory) {

    $type = $faker->randomElement(Section::getContentTypes());

    switch ($type) {
        case RichTextContent::CONTENT_TYPE:
            return $factory->rawOf(Section::class, RichTextContent::CONTENT_TYPE);
            break;
        case BlockquoteContent::CONTENT_TYPE:
            return $factory->rawOf(Section::class, BlockquoteContent::CONTENT_TYPE);
            break;
        case ImageContent::CONTENT_TYPE:
            return $factory->rawOf(Section::class, ImageContent::CONTENT_TYPE);
            break;
    }

});