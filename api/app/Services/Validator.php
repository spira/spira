<?php


namespace App\Services;


use App\Models\Relations\GateTrait;
use Spira\Core\Validation\SpiraValidator;

class Validator extends SpiraValidator
{
    use GateTrait;

    public function validateRbacRoleExists($attribute, $value, $parameters)
    {
        return (bool) $this->getGate()->getStorage()->getItem($value);
    }
}