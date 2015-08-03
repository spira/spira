<?php namespace App\Models;

class SocialLogin extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['provider', 'token'];

    /**
     * Overrides UuidTrait boot method.
     *
     * We override it with an empty method as we simply just want to prevent the
     * event registration to automatically create a UUID primary key, as we do
     * not use that in this model.
     *
     * @return void
     */
    protected static function bootUuidTrait()
    {
    }
}
