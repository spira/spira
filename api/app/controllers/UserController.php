<?php

class UserController extends BaseController
{
    public function getAll()
    {
        return User::all();
    }
}