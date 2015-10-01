<?php
use Spira\Rbac\Item\Rule;


/**
 * Checks if authorID matches userID passed via params
 */
class AuthorRule extends Rule
{
    public $name = 'isAuthor';
    public $reallyReally = false;

    /**
     * @inheritdoc
     */
    public function execute(callable $userResolver, $params)
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $user = $userResolver();
        return $params['authorID'] == $user->getAuthIdentifier();
    }
}
