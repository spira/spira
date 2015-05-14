<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;

class User extends BaseModel implements UserInterface {

	use UserTrait;


    const PRIMARY_KEY = 'user_id';

    protected $primaryKey = self::PRIMARY_KEY;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

}
