<?php namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\ConnectionResolverInterface as Connection;

class UserRepository extends BaseRepository
{
    /**
     * Login token time to live in minutes.
     *
     * @var int
     */
    protected $login_token_ttl = 1440;

    /**
     * Cache repository.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Assign dependencies.
     *
     * @param  Connection  $connection
     * @param  Cache       $cache
     * @return void
     */
    public function __construct(Connection $connection, Cache $cache)
    {
        parent::__construct($connection);

        $this->cache = $cache;
    }

    /**
     * Model name.
     *
     * @return User
     */
    protected function model()
    {
        return new User;
    }

    /**
     * Get a user by single use login token.
     *
     * @param string $token
     *
     * @return mixed
     */
    public function findByLoginToken($token)
    {
        if ($id = $this->cache->pull('login_token_'.$token)) {
            $user = $this->find($id);

            return $user;
        }
    }

    /**
     * Make a single use login token for a user.
     *
     * @param string $id
     *
     * @return string
     */
    public function makeLoginToken($id)
    {
        $user = $this->find($id);

        $token = hash_hmac('sha256', str_random(40), str_random(40));
        $this->cache->put('login_token_'.$token, $user->user_id, $this->login_token_ttl);

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

        // Set new users to guest
        $data['user_type'] = 'guest';

        $self = parent::createOrReplace($id, $data);

        // Create the credentials
        $this->find($id)->userCredential()->create($credential);

        return $self;
    }
}
