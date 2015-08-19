<?php
/**
 * Created by PhpStorm.
 * User: redjik
 * Date: 05.08.15
 * Time: 19:22
 */

namespace Spira\Model\Validation;

use Spira\Model\Model\BaseModel;

class Validator extends \Illuminate\Validation\Validator
{
    /**
     * @var BaseModel
     */
    protected $model;

    /**
     * @param BaseModel $model
     */
    public function setModel(BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * Replace all error message place-holders with actual values.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array   $parameters
     * @return string
     */
    protected function doReplacements($message, $attribute, $rule, $parameters)
    {
        return new TypeAwareMessage(parent::doReplacements($message, $attribute, $rule, $parameters), $rule);
    }
}
