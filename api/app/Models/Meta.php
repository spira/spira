<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

use Spira\Core\Model\Model\BaseModel;

class Meta extends BaseModel
{
    public $table = 'metas';

    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'meta_id',
        'meta_name',
        'meta_content',
    ];

    public static $metaableModels = [
        Article::class,
    ];

    public static function getValidationRules($entityId = null, array $requestEntity = [])
    {
        return [
            'meta_id' => 'uuid',
            'meta_name' => 'required|string',
            'meta_content' => 'string',
            'metaable_id' => 'unique:metas,metaable_id,'.$entityId.',meta_id,meta_name,'.$requestEntity['meta_name']
        ];
    }

    public function metaableModel()
    {
        return $this->morphTo();
    }
}
