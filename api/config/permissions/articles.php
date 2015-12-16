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
use App\Http\Auth\ReAssignNonAdmin;
use App\Http\Auth\ManipulateWithOwn;
use App\Http\Auth\ImpersonateNonAdmin;
use App\Http\Auth\ManipulateWithOwnChild;

return [

    //basic route based permissions
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
];
