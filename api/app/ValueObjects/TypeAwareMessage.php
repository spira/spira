<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 27.07.15
 * Time: 23:53
 */

namespace App\ValueObjects;


class TypeAwareMessage
{
    public $message;
    public $type;

    public function __construct($message, $type)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function __toString()
    {
        return $this->message;
    }
}