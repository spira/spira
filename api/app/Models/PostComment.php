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
 * model of PostComment.
 *
 * @property string $post_comment_id
 * @property string $body
 * @property \DateTime $created_at
 */
class PostComment extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_comment_id',
        'body',
        'created_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get validation rules.
     *
     * @return array
     */
    public static function getValidationRules($entityId = null)
    {
        return [
            'user_id' => 'required|uuid',
            'body' => 'required|string',
        ];
    }

    /**
     * Set comment author.
     *
     * @param  User $user
     *
     * @return $this
     */
    public function setAuthor(User $user)
    {
        $attributes = ['user_id', 'username', 'first_name', 'last_name', 'avatar_img_url'];

        $this->_author = array_only($user->toArray(), $attributes);

        return $this;
    }

    /**
     * Post comments finding shouldn't be attempted by the hydrator on create as they can't be found from the database.
     * @param $id
     * @param array $columns
     * @return BaseModel|void
     */
    public static function find($id, $columns = ['*'])
    {
        return;
    }

}
