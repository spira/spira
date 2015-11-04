<?php

namespace App\Models;

use Spira\Model\Model\BaseModel;

class Permission extends BaseModel
{
    protected $primaryKey = 'key';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key','description'];
}