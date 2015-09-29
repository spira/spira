<?php

namespace Spira\Rbac\Access;

use Spira\Contract\Exception\ForbiddenException;

trait AuthorizesRequestsTrait
{
    protected $defaultPermission = null;

    /**
     * Authorize a given action against a set of arguments.
     *
     * @param  mixed  $ability
     * @param  mixed|array  $arguments
     * @return void
     *
     * @throws ForbiddenException
     */
    public function authorize($ability, $arguments = [])
    {
        $permission = null;
        if (is_string($ability)){
            $permission = $ability;
        }else{
            $arguments['model'] = $ability;
        }

        if (is_null($permission) && is_null($this->defaultPermission)){
            return;
        }

        if ((!$permission) && $this->defaultPermission){
            $permission = $this->defaultPermission.'@'.$this->getAction();
        }

        if (!$permission){
            $permission = static::class.'@'.$this->getAction();
        }

        if (! $this->getGate()->check($permission, $arguments)) {
            throw $this->createGateUnauthorizedException($ability, $arguments);
        }
    }


    protected function getAction()
    {
        return debug_backtrace(false, 3)[2]['function'];
    }


    /**
     * @return Gate
     */
    public function getGate()
    {
        return app(Gate::GATE_NAME);
    }

    /**
     * Throw an unauthorized exception based on gate results.
     *
     * @param  string  $ability
     * @param  array  $arguments
     * @return ForbiddenException
     */
    protected function createGateUnauthorizedException($ability, $arguments)
    {
        return new ForbiddenException();
    }
}