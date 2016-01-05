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
        'description' => 'Get one',
    ],
    ArticleController::class.'@getAllLocalizations' => [
        'type' => 'permission',
        'description' => 'Get localizations',
    ],
    ArticleController::class.'@getOneLocalization' => [
        'type' => 'permission',
        'description' => 'Get localization',
    ],
    ArticleController::class.'@postOne' => [
        'type' => 'permission',
        'description' => 'Post',
    ],
    ArticleController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Put',
    ],
    ArticleController::class.'@patchOne' => [
        'type' => 'permission',
        'description' => 'Patch',
    ],
    ArticleController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete',
    ],
    ArticleController::class.'@putOneLocalization' => [
        'type' => 'permission',
        'description' => 'Put localization',
    ],
    ArticleController::class.'@syncMany' => [
        'type' => 'permission',
        'description' => 'Sync',
    ],
    ArticlePermalinkController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get permalinks',
    ],
    ArticleMetaController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all meta',
    ],
    ArticleMetaController::class.'@putMany' => [
        'type' => 'permission',
        'description' => 'Add meta',
    ],
    ArticleMetaController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete meta',
    ],
    ArticleCommentController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all comments',
    ],
    ArticleCommentController::class.'@postOne' => [
        'type' => 'permission',
        'description' => 'Post comment',
    ],

    ArticleTagController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all tags',
    ],
    ArticleTagController::class.'@putMany' => [
        'type' => 'permission',
        'description' => 'Add tags',
    ],

    ArticleSectionController::class.'@getAll' => [
        'type' => 'permission',
        'description' => 'Get all sections',
    ],
    ArticleSectionController::class.'@postMany' => [
        'type' => 'permission',
        'description' => 'Post',
    ],
    ArticleSectionController::class.'@deleteMany' => [
        'type' => 'permission',
        'description' => 'Delete many',
    ],
    ArticleSectionController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Delete one',
    ],
    ArticleSectionController::class.'@putOneChildLocalization' => [
        'type' => 'permission',
        'description' => 'Put section localization',
    ],
    
    
    ArticleUserRatingsController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Rate article or change rating value',
    ],
    ArticleBookmarksController::class.'@putOne' => [
        'type' => 'permission',
        'description' => 'Add to bookmarks',
    ],
    ArticleUserRatingsController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Remove article rating',
    ],
    ArticleBookmarksController::class.'@deleteOne' => [
        'type' => 'permission',
        'description' => 'Remove from bookmarks',
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
            ArticleSectionController::class.'@putOneChildLocalization'

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
            ArticleController::class.'@getAllTagCategories'
        ],
    ],
];
