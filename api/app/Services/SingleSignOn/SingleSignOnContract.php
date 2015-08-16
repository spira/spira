<?php

namespace App\Services\SingleSignOn;

interface SingleSignOnContract
{
    /**
     * Get the response to the requester.
     *
     * @return mixed
     */
    public function getResponse();
}
