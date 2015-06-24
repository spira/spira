<?php namespace App\Http\Validators;

use App\Services\Validator;

class TestValidator extends Validator
{
    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [
        'varchar' => 'required|string',
        'hash' => 'required|string',
        'integer' => 'required|integer',
        'decimal' => 'required|float',
        'boolean' => 'required|boolean',
        'text' => 'required|string',
        'date' => 'required|date',
        'multi_word_column_title' => 'required|boolean',
        'hidden' => 'required|boolean'
    ];

    /**
     * Validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }

    /**
     * Modify the rules for put operations.
     *
     * @return $this
     */
    public function put()
    {
        $this->rules = array_add($this->rules, 'entity_id', 'required|uuid');

        return $this;
    }
}
