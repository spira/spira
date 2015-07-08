<?php namespace App\Repositories;

use Cache;
use Illuminate\Container\Container as App;

class UserRepository extends BaseRepository
{
    /**
     * Login token time to live in minutes.
     *
     * @var integer
     */
    protected $login_token_ttl = 1440;

    /**
     * Model name.
     *
     * @return string
     */
    protected function model()
    {
        return 'App\Models\User';
    }

    /**
     * Get a user by single use login token.
     *
     * @param  string  $token
     * @return mixed
     */
    public function findByLoginToken($token)
    {
        if ($id = Cache::pull('login_token_'.$token)) {
            $user = $this->find($id);
            return $user;
        }
    }

    /**
     * Make a single use login token for a user.
     *
     * @param  string  $id
     * @return string
     */
    public function makeLoginToken($id)
    {
        $user = $this->find($id);

        $token = hash_hmac('sha256', str_random(40), str_random(40));
        Cache::put('login_token_'.$token, $user->user_id, $this->login_token_ttl);

        return $token;
    }

    /**
     * Create or replace an entity by id.
     *
     * @param  string  $id
     * @param  array   $data
     * @return array
     */
    public function createOrReplace($id, array $data)
    {
        // Extract the credentials
        $credential = array_pull($data, '#user_credential');

        // Set new users to public
        $data['user_type'] = 'public';

        $self = parent::createOrreplace($id, $data);

        // Create the credentials
        $this->find($id)->userCredential()->create($credential);

        return $self;
    }
}
