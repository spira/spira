<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Models\Section;
use Spira\Core\Controllers\ChildEntityController;
use Spira\Core\Model\Model\BaseModel;

class AbstractSectionController extends ChildEntityController
{
    protected $relationName = 'sections';

}
