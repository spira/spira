<?php

namespace Spira\Model\Validation;

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
