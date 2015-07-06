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
}
