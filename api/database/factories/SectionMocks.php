<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use App\Models\PostSectionsDisplay;
use Faker\Generator;
use App\Models\User;
use App\Models\Image;
use App\Models\Section;
use App\Models\Sections\MediaContent;
use App\Models\Sections\PromoContent;
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

$factory->define(MediaContent::class, function (Generator $faker) {

    $images = Image::all();

    $media = array_map(function () use ($faker, $images) {
        $type = $faker->randomElement(MediaContent::$mediaTypes);

        $mediaItem = [
            'type' => $type,
        ];

        switch ($type) {
            case MediaContent::MEDIA_TYPE_IMAGE:
                $mediaItem = array_merge($mediaItem, [
                    '_image' => $images->random(1)->toArray(),
                    'caption' => $faker->sentence(),
                    'transformations' => null,
                ]);
                break;
            case MediaContent::MEDIA_TYPE_VIDEO:
                $mediaItem = array_merge($mediaItem, [
                    'provider' => $provider = $faker->randomElement(MediaContent::$videoProviders),
                    'video_id' => $provider == MediaContent::VIDEO_PROVIDER_VIMEO ? $faker->numerify('########') : substr($faker->shuffle('1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM'), 0, 11),
                    'caption' => $faker->optional()->sentence(),
                ]);
                break;
        }

        return $mediaItem;

    }, array_fill(0, $faker->numberBetween(1, 5), null));

    return [
        'media' => $media,
        'size' => $faker->randomElement(MediaContent::$sizeOptions),
        'alignment' => $faker->randomElement(MediaContent::$alignmentOptions),
    ];

});

$factory->define(PromoContent::class, function (Generator $faker) {

    return [];
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

$factory->defineAs(Section::class, MediaContent::CONTENT_TYPE, function (Generator $faker) {

    return [
        'section_id' => $faker->uuid,
        'type' => MediaContent::CONTENT_TYPE,
        'content' => factory(MediaContent::class)->make(),
    ];

});

$factory->defineAs(Section::class, PromoContent::CONTENT_TYPE, function (Generator $faker) {

    return [
        'section_id' => $faker->uuid,
        'type' => PromoContent::CONTENT_TYPE,
        'content' => factory(PromoContent::class)->make(),
    ];

});

$factory->define(PostSectionsDisplay::class, function (Generator $faker) {
    return [
        'sort_order' => [],
    ];
});

$factory->define(Section::class, function (Generator $faker) use ($factory) {

    $type = $faker->randomElement(Section::getContentTypes());

    switch ($type) {
        case BlockquoteContent::CONTENT_TYPE:
            return $factory->rawOf(Section::class, BlockquoteContent::CONTENT_TYPE);
            break;
        case MediaContent::CONTENT_TYPE:
            return $factory->rawOf(Section::class, MediaContent::CONTENT_TYPE);
            break;
        case PromoContent::CONTENT_TYPE:
            return $factory->rawOf(Section::class, PromoContent::CONTENT_TYPE);
            break;
        default:
            return $factory->rawOf(Section::class, RichTextContent::CONTENT_TYPE);
            break;

    }

});
