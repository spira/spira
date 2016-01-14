<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Role;

return [

    //basic route based permissions
    //basic route based permissions
    ArticleController::class.'@getAllTagCategories' => [
        'type' => 'permission',
        'description' => 'Tag categories',
    ],
    ArticleController::class.'@getAllPaginated' => [
        'type' => 'permission',
        'description' => 'Get all paginated',
    ],
    ArticleController::class.'@getOne' => [
        'type' => 'permission',
        'description' => 'Get one article',
    ],
    ArticleController::class.'@getAllLocalizations' => [
        'type' => 'permission',
        'description' => 'Get all localizations for article',
    ],
    ArticleController::class.'@getOneLocalization' => [
        'type' => 'permission',
        'description' => 'Get localization for article',
    ],
    ArticleController::class.'@postOne' => [
        'type' => 'permission',
        'description' => 'Create new article',
    ],
    ArticleController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Create new article',
    ],
    ArticleController::class.'@patchOne' => [
        'type' => 'permission',
        'description' => 'Update article',
    ],
    ArticleController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete article',
    ],
    ArticleController::class.'@putOneLocalization' => [
        'type' => 'permission',
        'description' => 'Add localization to article',
    ],
    ArticlePermalinkController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all article permalinks',
    ],
    ArticleMetaController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all article meta',
    ],
    ArticleMetaController::class.'@putMany' => [
        'type' => 'permission',
        'description' => 'Add article meta',
    ],
    ArticleMetaController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete article meta',
    ],
    ArticleCommentController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all article comments',
    ],
    ArticleCommentController::class.'@postOne' => [
        'type' => 'permission',
        'description' => 'Post article comment',
    ],

    ArticleTagController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all article tags',
    ],
    ArticleTagController::class.'@putMany' => [
        'type' => 'permission',
        'description' => 'Add article tags',
    ],

    ArticleSectionController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all article sections',
    ],
    ArticleSectionController::class.'@postMany' => [
        'type' => 'permission',
        'description' => 'Post',
    ],
    ArticleSectionController::class.'@deleteMany' => [
        'type' => 'permission',
        'description' => 'Delete many article sections',
    ],
    ArticleSectionController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete one article section',
    ],
    ArticleSectionController::class.'@putOneChildLocalization' => [
        'type' => 'permission',
        'description' => 'Add section localization to article',
    ],

    ArticleUserRatingsController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Rate article or change rating value',
    ],
    ArticleBookmarksController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Add article to bookmarks',
    ],
    ArticleUserRatingsController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Remove article rating',
    ],
    ArticleBookmarksController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Remove article from bookmarks',
    ],

    //special permissions (hierarchy or rules)
    'ManipulateWithOwnChild' => [
        'children' => [
            ArticleUserRatingsController::class.'@putOne',
            ArticleBookmarksController::class.'@putOne',
            ArticleUserRatingsController::class.'@deleteOne',
            ArticleBookmarksController::class.'@deleteOne',
        ],
    ],

    // Roles

    Role::ADMIN_ROLE => [
        'children' => [
            ArticleController::class.'@postOne',
            ArticleController::class.'@putOne',
            ArticleController::class.'@patchOne',
            ArticleController::class.'@deleteOne',
            ArticleController::class.'@putOneLocalization',
            ArticleController::class.'@syncMany',
            ArticleMetaController::class.'@putMany',
            ArticleMetaController::class.'@deleteOne',
            ArticleCommentController::class.'@postOne',
            ArticleTagController::class.'@putMany',
            ArticleSectionController::class.'@postMany',
            ArticleSectionController::class.'@deleteMany',
            ArticleSectionController::class.'@deleteOne',
            ArticleSectionController::class.'@putOneChildLocalization',

        ],
    ],
    Role::USER_ROLE => [
        'children' => [
            ArticleController::class.'@getAllPaginated',
            ArticleController::class.'@getOne',
            ArticleController::class.'@getAllLocalizations',
            ArticleController::class.'@getOneLocalization',
            ArticlePermalinkController::class.'@getAll',
            ArticleMetaController::class.'@getAll',
            ArticleCommentController::class.'@getAll',
            ArticleCommentController::class.'@postOne',
            ArticleTagController::class.'@getAll',
            ArticleSectionController::class.'@getAll',
            ArticleController::class.'@getAllTagCategories',
        ],
    ],
];
