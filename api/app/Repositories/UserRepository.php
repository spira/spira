<?php

namespace App\Repositories;

use Cache;
use Illuminate\Container\Container as App;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

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
     * @var CacheRepository
     */
    protected $cache;

    /**
     * Assign dependencies.
     *
     * @param App              $app
     * @param CacheRepository  $cache
     *
     * @return void
     */
    public function __construct(App $app, CacheRepository $cache)
    {
        $this->cache = $cache;

        parent::__construct($app);
    }

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


    /**
     * Update an entity by id.
     *
     * @param string $id
     * @param array  $data
     *
     * @throws App\Exceptions\FatalException
     *
     * @return mixed
     */
    public function update($id, array $data)
    {
        // Make sure the data does not contain a different id for the entity.
        $keyName = $this->model->getKeyName();
        if (array_key_exists($keyName, $data) and $id !== $data[$keyName]) {
            throw new FatalErrorException('Attempt to override entity ID value.');
        }

        $model = $this->find($id);

        // Before updating the data, check if the email has changed.
        // This shall probably be moved to the controller when the architecture
        // update is applied.
        if ($model->email != $data['email']) {
            $model->email_confirmed = null;
        }

        return $model->update($data);
    }
}
