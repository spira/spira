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

/**
 * @property string $permalink
 * @property Article article
 *
 * Class PostPermalink
 */
class PostPermalink extends BaseModel
{
    public $table = 'permalink_post';

    protected $primaryKey = 'permalink';

    protected $fillable = ['permalink'];

    protected $touches = ['article'];

    protected static $validationRules = [
        'permalink' => 'string|required',
        'check_entity_id' => 'uuid',
        'value' => 'required|string',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class, 'post_id', 'post_id');
    }
}
