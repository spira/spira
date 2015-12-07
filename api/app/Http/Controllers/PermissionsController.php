<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\RoleTransformer;
use App\Models\User;
use Spira\Core\Controllers\ChildEntityController;
use Spira\Core\Model\Collection\Collection;
use Spira\Core\Model\Model\BaseModel;

class PermissionsController extends ChildEntityController
{
    /**
     * Enable permissions checks.
     */
    protected $permissionsEnabled = true;

    protected $defaultRole = false;

    protected $relationName = 'roles';

    public function __construct(User $parentModel, RoleTransformer $transformer)
    {
        parent::__construct($parentModel, $transformer);
    }

    /**
     * @param $requestCollection
     * @param BaseModel $parent
     * @return Collection
     */
    protected function findChildrenCollection($requestCollection, BaseModel $parent)
    {
        return $parent->roles;
    }
}
