<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models\Sections;

use Spira\Core\Model\Model\VirtualModel;

class MediaContent extends VirtualModel
{
    const CONTENT_TYPE = 'media';
    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_VIDEO = 'video';

    const VIDEO_PROVIDER_YOUTUBE = 'youtube';
    const VIDEO_PROVIDER_VIMEO = 'vimeo';

    public static $videoProviders = [
        self::VIDEO_PROVIDER_VIMEO,
        self::VIDEO_PROVIDER_YOUTUBE,
    ];
    public static $mediaTypes = [
        self::MEDIA_TYPE_IMAGE,
        self::MEDIA_TYPE_VIDEO,
    ];

    public static $sizeOptions = [
        'small', 'half', 'full', 'oversize',
    ];

    public static $alignmentOptions = [
        'left', 'centre', 'right',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'media',
    ];
}
