<?php

namespace App\Extensions\Socialite\Parsers;

use Laravel\Socialite\Contracts\User;
use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractParser implements Arrayable
{
    use UsernameTrait;

    /**
     * User object to parse.
     *
     * @var User
     */
    protected $user;

    /**
     * The parsed attributes.
     *
     * @var array
     */
    protected $attributes = ['token', 'email', 'username', 'first_name', 'last_name', 'avatar_img_url'];

    /**
     * Initialize the parser.
     *
     * @param  User  $user
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        $this->parse();
    }

    /**
     * Get the user's token.
     *
     * @return string
     */
    abstract protected function getTokenAttribute();

    /**
     * Get the user's email address.
     *
     * @return string
     */
    abstract protected function getEmailAttribute();

    /**
     * Get the user's first name.
     *
     * @return string
     */
    abstract protected function getFirstNameAttribute();

    /**
     * Get the user's last name.
     *
     * @return string
     */
    abstract protected function getLastNameAttribute();

    /**
     * Get the user's avatar.
     *
     * @return string
     */
    abstract protected function getAvatarImgUrlAttribute();

    /**
     * Parse the social user object.
     *
     * @return void
     */
    protected function parse()
    {
        $this->attributes = array_fill_keys($this->attributes, '');

        foreach (array_keys($this->attributes) as $attribute) {
            // Get the attribute
            $method = camel_case('get_'.$attribute.'_attribute');
            if (method_exists($this, $method)) {
                $this->attributes[$attribute] = $value = $this->$method();
            }

            // Filter the attribute
            $method = camel_case('filter_'.$attribute.'_attribute');
            if (method_exists($this, $method)) {
                $this->attributes[$attribute] = $this->$method($value);
            }
        }
    }

    /**
     * Convert the parsed user to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Dynamically retrieve parsed user attributes.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    /**
     * Dynamically set user attributes.
     *
     * @param  string  $key
     * @param  mixed   $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }
}
