<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\User;
use Spira\Core\Controllers\ChildEntityController;
use Spira\Core\Responder\Transformers\EloquentModelTransformer;

class UserProfileController extends ChildEntityController
{
    /**
     * Enable permissions checks.
     */
    protected $permissionsEnabled = true;
    protected $defaultRole = false;

    protected $relationName = 'userProfile';

    public function __construct(User $parentModel, EloquentModelTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }
}
