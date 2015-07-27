<?php namespace App\Services;

use App\Models\BaseModel;
use App\ValueObjects\TypeAwareMessage;
use Illuminate\Validation\Validator;

class SpiraValidator extends Validator
{
    /**
     * @var BaseModel
     */
    protected $model;

    public function validateFloat($attribute, $value, $parameters)
    {
        return is_float($value + 0);
    }

    public function validateUuid($attribute, $value, $parameters)
    {
        return preg_match('/^\{?[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}\}?$/', $value);
    }

    public function validateCreateOnly($attribute, $value, $parameters)
    {
        $original = $this->model->getOriginal($attribute);
        if (is_null($original)) {
            return true;
        }

        if ($original == $value) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseModel $model
     */
    public function setModel(BaseModel $model)
    {
        $this->model = $model;
    }

    /**
     * Register custom validation rule for countries.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array   $parameters
     * @return void
     */
    protected function validateCountry($attribute, $value, $parameters)
    {
        $countries = \App::make('App\Services\Datasets\Countries')
            ->all()
            ->toArray();

        return in_array($value, array_fetch($countries, 'country_code'));
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
