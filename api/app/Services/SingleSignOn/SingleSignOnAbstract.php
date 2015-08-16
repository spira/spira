<?php

namespace App\Services\SingleSignOn;

use Illuminate\Http\Request;

abstract class SingleSignOnAbstract
{
    /**
     * The request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * The user to sign on.
     *
     * @var mixed
     */
    protected $user;

    /**
     * Assign dependencies.
     *
     * @param  Request  $request
     * @param  mixed    $user
     *
     * @return void
     */
    public function __construct(Request $request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }
}
