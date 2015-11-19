<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Spira\Model\Model\BaseModel;

class Meta extends BaseModel
{
    public $table = 'meta';

    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'meta_id',
        'meta_name',
        'meta_content',
    ];

    public static $metaableModels = [
        Article::class,
    ];

    protected static $validationRules = [
        'meta_id' => 'uuid',
        'meta_name' => 'required|string',
        'meta_content' => 'string',
    ];

    public function metaableModel()
    {
        return $this->morphTo();
    }

}
