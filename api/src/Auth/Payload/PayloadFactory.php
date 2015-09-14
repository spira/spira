<?php
/**
 * Created by PhpStorm.
 * User: ivanmatveev
 * Date: 13.09.15
 * Time: 20:49
 */

namespace Spira\Auth\Payload;


use Illuminate\Contracts\Auth\Authenticatable;

class PayloadFactory
{
    /**
     * @var array
     */
    protected $payloadGenerators;

    public function __construct(array $payloadGenerators = [])
    {
        $this->payloadGenerators = $payloadGenerators;
    }

    public function createFromUser(Authenticatable $user)
    {
        $payload = [];
        foreach ($this->payloadGenerators as $name => $generator) {
            if ($generator instanceof \Closure){
                $result = $generator($user);
                if ($result !== false ){
                    $payload[$name] = $result;
                }
            }
        }

        return $payload;
    }

    public function addPayloadGenerator($name, \Closure $function)
    {
        $this->payloadGenerators[$name] = $function;
    }
}