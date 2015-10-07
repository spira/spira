<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spira\Rbac\Storage;

use Spira\Rbac\Item\Assignment;
use Spira\Rbac\Item\Item;
use Spira\Rbac\Item\Role;

interface StorageInterface extends AssignmentStorageInterface, ItemStorageInterface
{

}
