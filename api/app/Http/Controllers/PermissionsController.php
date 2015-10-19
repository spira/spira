<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Http\Transformers\PermissionsTransformer;
use Spira\Rbac\Item\Assignment;

class PermissionsController extends ApiController
{
    /**
     * Enable permissions checks.
     */
    protected $permissionsEnabled = true;

    protected $defaultRole = false;

    public function __construct(PermissionsTransformer $transformer)
    {
        parent::__construct($transformer);
    }

    public function getUserRoles($id)
    {
        $assignments = $this->getGate()->getStorage()->getAssignments($id);

        foreach ($this->getGate()->getDefaultRoles() as $role) {
            if (! isset($assignments[$role])) {
                $defaultAssignment = new Assignment();
                $defaultAssignment->roleName = $role;
                $defaultAssignment->userId = $id;
                $assignments[$role] = $defaultAssignment;
            }
        }

        $this->authorize(static::class.'@getUserRoles', ['model' => (object) ['user_id' => $id]]);

        return $this->getResponse()
            ->transformer($this->getTransformer())
            ->collection($assignments);
    }
}
