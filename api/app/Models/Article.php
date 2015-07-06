<?php namespace App\Models;

class Article extends BaseModel {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'articles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['article_id', 'title', 'content', 'permalink', 'first_published'];

    protected $primaryKey = 'article_id';

    /**
     * Get the access route for the entity.
     *
     * @return string
     */
    public function entityRoute()
    {
        return '/articles';
    }

    protected $casts = [
        'first_published' => 'dateTime',
    ];

}