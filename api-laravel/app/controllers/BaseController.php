<?php

use Illuminate\Routing\Controller;
use Dingo\Api\Routing\ControllerTrait;

class BaseController extends Controller {

    use ControllerTrait;

    public function __construct()
    {
        //@todo add filter to make all keys in json camelCased
    }


    protected function setupLayout()
    {
        if ( ! is_null($this->layout))
        {
            $this->layout = View::make($this->layout);
        }
    }

    public function notFound() {
        return $this->response->error('Not Found', 404);
    }


}
