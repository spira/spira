<?php

namespace App\Models;

use Spira\Model\Model\BaseModel;

class ArticleComment extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'article_comment_id',
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
    public static function getValidationRules()
    {
        return [
            'user_id' => 'required|uuid',
            'content' => 'required|string',
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
}
