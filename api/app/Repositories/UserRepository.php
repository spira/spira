<?php namespace App\Repositories;

class UserRepository extends BaseRepository
{
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
        $user = $this->model->loginToken($token)->first();

        // If we found a user, erase the token so it can't be used again
        if ($user) {
            $user->login_token = null;
            $user->save();
        }

        return $user;
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

        $user->login_token = hash_hmac('sha256', str_random(40), str_random(40));
        $user->save();

        return $user->login_token;
    }
}
