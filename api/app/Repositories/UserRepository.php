<?php namespace App\Repositories;

use App\Models\User;
use App\Jobs\SendEmailConfirmationEmail;
use Laravel\Lumen\Routing\DispatchesJobs;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\ConnectionResolverInterface as Connection;

class UserRepository extends BaseRepository
{
    use DispatchesJobs;

    /**
     * Login token time to live in minutes.
     *
     * @var int
     */
    protected $login_token_ttl = 1440;

    /**
     * Confirmation token time to live in minutes.
     *
     * @var int
     */
    protected $confirmation_token_ttl = 1440;

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
     * Make an email confirmation token for a user.
     *
     * @param string $email
     *
     * @return string
     */
    public function makeConfirmationToken($email)
    {
        $token = hash_hmac('sha256', str_random(40), str_random(40));
        $this->cache->put('email_confirmation_'.$token, $email, $this->confirmation_token_ttl);
        return $token;
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
        if (isset($data['email']) and $model->email != $data['email']) {
            $token = $this->makeConfirmationToken($data['email']);
            $this->dispatch(new SendEmailConfirmationEmail($model, $data['email'], $token));
            $data['email_confirmed'] = null;
        }
        return $model->update($data);
    }
}
